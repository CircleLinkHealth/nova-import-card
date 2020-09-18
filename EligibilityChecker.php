<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility;

use CircleLinkHealth\Customer\CpmConstants;
use Carbon\Carbon;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Adapters\JsonMedicalRecordInsurancePlansAdapter;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\PcmProblem;
use CircleLinkHealth\Eligibility\Entities\Problem;
use CircleLinkHealth\Eligibility\Exceptions\InvalidStructureException;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\SnomedToCpmIcdMap;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Validator;

/**
 * @property null medicalRecordType
 */
class EligibilityChecker
{
    use ValidatesEligibility;

    /**
     * Create PreEnrollees.
     *
     * @var bool
     */
    public $createEnrollees;

    /**
     * Filter the list by Insurance.
     *
     * @var bool
     */
    public $filterInsurance;

    /**
     * Filter the list by Last Encounter.
     *
     * @var bool
     */
    public $filterLastEncounter;

    /**
     * Filter the list by the number of CCM problems. Eligible Patients need to have at least 2.
     *
     * @var bool
     */
    public $filterProblems;

    /**
     * @var null
     */
    private $batch;
    /**
     * @var EligibilityJob
     */
    private $eligibilityJob;
    private $enrollee;

    /**
     * @var Practice
     */
    private $practice;

    /**
     * EligibilityChecker constructor.
     *
     * @param bool $filterLastEncounter
     * @param bool $filterInsurance
     * @param bool $filterProblems
     * @param bool $createEnrollees
     *
     * @throws \Exception
     */
    public function __construct(
        EligibilityJob &$eligibilityJob,
        Practice $practice,
        EligibilityBatch &$batch,
        $filterLastEncounter = false,
        $filterInsurance = false,
        $filterProblems = true,
        $createEnrollees = true
    ) {
        $this->filterLastEncounter = $filterLastEncounter;
        $this->filterInsurance     = $filterInsurance;
        $this->filterProblems      = $filterProblems;
        $this->createEnrollees     = $createEnrollees;
        $this->practice            = $practice;
        $this->batch               = $batch;
        $this->eligibilityJob      = $eligibilityJob;

        if ($this->eligibilityJob) {
            $this->eligibilityJob->status = 1;
            $this->eligibilityJob->sanitizeDataKeys();
            $this->eligibilityJob->save();
        }

        try {
            $isValid = $this->filter();

            if ($isValid) {
                $this->eligibilityJob->status = EligibilityJob::ELIGIBLE;

                if ($this->createEnrollees) {
                    $this->createEnrollee();
                }
            }
        } catch (\Exception $e) {
            if ($this->eligibilityJob) {
                $this->setEligibilityJobStatusFromException($e);

                $this->eligibilityJob->save();
            }

            throw $e;
        }
    }

    public function __destruct()
    {
        if ($this->batch) {
            $this->batch->touch();
        }

        if ($this->eligibilityJob) {
            try {
                $this->eligibilityJob->save();

                if ($tP = $this->eligibilityJob->targetPatient) {
                    $tP->status = $this->eligibilityJob->status;
                    $tP->save();
                }

                if ($ccd = optional($tP)->ccda) {
                    $ccd->status = $this->eligibilityJob->status;
                    $ccd->save();
                }
            } catch (\Exception $e) {
                \Log::critical($e);
                \Log::critical($this->eligibilityJob);

                throw new \Exception("Eligibility processing exception: {$e->getMessage()}");
            }
        }
    }

    public function getEligibilityJob()
    {
        return $this->eligibilityJob;
    }

    public function getEnrollee()
    {
        return $this->enrollee;
    }

    public function getPatientList(): Collection
    {
        return $this->patientList;
    }

    public static function getProblemsForEligibility(EligibilityJob $eligibilityJob)
    {
        $problems = $eligibilityJob->data['problems'] ?? $eligibilityJob->data['problems_string'] ?? null;

        if (empty($problems)) {
            return false;
        }

        foreach (config('importer.problem_loggers') as $class) {
            $class = app($class);

            if ($class->shouldHandle($problems)) {
                $problems = $class->handle($problems);
                break;
            }
        }

        return $problems;
    }

    /**
     * @throws \Exception
     */
    protected function filter()
    {
        $this->validateStructureAndData();

        $isValid = null;

        if ($this->filterProblems) {
            $isValid = $this->validateProblems();
        }

        if ($this->filterLastEncounter && false !== $isValid) {
            $isValid = $this->validateLastEncounter();
        }

        if ($this->filterInsurance && false !== $isValid) {
            $isValid = $this->validateInsurance();
        }

        return $isValid;
    }

    protected function validateInsurance(): bool
    {
        if (isset($this->eligibilityJob->data['insurance_plans']) || isset($this->eligibilityJob->data['insurance_plan'])) {
            //adapt record so that we can use validateInsuranceWithPrimarySecondaryTertiary()
            $this->eligibilityJob->data = $this->adaptClhFormatInsurancePlansToPrimaryAndSecondary(
                $this->eligibilityJob->data
            );
        }

        if (isset($this->eligibilityJob->data['insurances'])) {
            return $this->validateInsuranceWithCollection($this->eligibilityJob->data);
        }
        if (isset($this->eligibilityJob->data['primary_insurance']) || isset($this->eligibilityJob->data['secondary_insurance']) || isset($this->eligibilityJob->data['tertiary_insurance'])) {
            return $this->validateInsuranceWithPrimarySecondaryTertiary($this->eligibilityJob->data);
        }

        $this->setEligibilityJobStatus(
            3,
            ['insurance' => 'No insurance plans found.'],
            EligibilityJob::INELIGIBLE,
            'insurance'
        );

        return false;
    }

    /**
     * Removes Patients whose last encounter was before Feb. 1st, 2016 from the list.
     */
    protected function validateLastEncounter(): bool
    {
        //Anything past this date is valid
        $minEligibleDate = Carbon::now()->subYear();

        $possibleNames = [
            'last_encounter',
            'last_visit',
        ];

        foreach ($possibleNames as $n) {
            if (isset($this->eligibilityJob->data[$n])) {
                $lastEncounter = $this->eligibilityJob->data[$n];
            }
        }

        if ( ! isset($lastEncounter) || 'null' == strtolower($lastEncounter)) {
            $this->setEligibilityJobStatus(
                3,
                ['last_encounter' => 'Last encounter field not found, or is null.'],
                EligibilityJob::INELIGIBLE
            );

            return false;
        }

        $validator = Validator::make(
            [
                'last_encounter' => $lastEncounter,
            ],
            [
                'last_encounter' => 'required|filled|date',
            ]
        );

        if ($validator->fails()) {
            $this->setEligibilityJobStatus(
                3,
                [
                    'last_encounter' => implode(
                        ',',
                        $validator->messages()->all()
                    )." value: `${lastEncounter}`",
                ],
                EligibilityJob::INELIGIBLE,
                'last_encounter'
            );

            return false;
        }

        $lastEncounterDate = is_a($lastEncounter, Carbon::class)
            ? $lastEncounter
            : new Carbon($lastEncounter);

        if ($this->eligibilityJob) {
            $this->eligibilityJob->last_encounter = $lastEncounterDate;
        }

        if ($lastEncounterDate->lt($minEligibleDate)) {
            $this->setEligibilityJobStatus(
                3,
                ['last_encounter' => "Patient last encounter `{$lastEncounterDate->toDateString()}` is more than a year ago."],
                EligibilityJob::INELIGIBLE,
                'last_encounter'
            );

            return false;
        }

        return true;
    }

    protected function validateProblems(): bool
    {
        $cpmProblems = \Cache::remember(
            'all_cpm_problems',
            2,
            function () {
                return CpmProblem::all()
                    ->transform(
                        function ($problem) {
                            $problem->searchKeywords = collect(
                                explode(',', $problem->contains),
                                [$problem->name]
                            )
                                ->transform(
                                    function ($keyword) {
                                        return trim(strtolower($keyword));
                                    }
                                )
                                ->filter()
                                ->unique()
                                ->values()
                                ->toArray();

                            return $problem;
                        }
                    );
            }
        );

        $icd9Map = \Cache::remember(
            'map_icd_9_to_cpm_problems',
            2,
            function () {
                return $this->getSnomedToIcdMap()->pluck('cpm_problem_id', CpmConstants::ICD9);
            }
        );

        $icd10Map = \Cache::remember(
            'map_icd_10_to_cpm_problems',
            2,
            function () {
                return $this->getSnomedToIcdMap()->pluck('cpm_problem_id', CpmConstants::ICD10);
            }
        );

        $snomedMap = \Cache::remember(
            'map_snomed_to_cpm_problems',
            2,
            function () {
                return $this->getSnomedToIcdMap()->pluck('cpm_problem_id', CpmConstants::SNOMED);
            }
        );

        $cpmProblemsMap = \Cache::remember(
            'map_name_to_cpm_problems',
            2,
            function () use ($cpmProblems) {
                return $cpmProblems->pluck('name', 'id');
            }
        );

        $allBhiProblemIds = \Cache::remember(
            'bhi_cpm_problem_ids',
            2,
            function () use ($cpmProblems) {
                return $cpmProblems->where('is_behavioral', '=', true)->pluck('id');
            }
        );

        $eligibilityJobData = $this->eligibilityJob->data;

        $eligibilityJobData['ccm_condition_1'] = '';
        $eligibilityJobData['ccm_condition_2'] = '';
        $eligibilityJobData['cpm_problem_1']   = '';
        $eligibilityJobData['cpm_problem_2']   = '';

        $problems = self::getProblemsForEligibility($this->eligibilityJob);

        if (false === $problems) {
            $this->setEligibilityJobStatus(
                3,
                ['problems' => 'Patient has 0 conditions.'],
                EligibilityJob::INELIGIBLE,
                'problems'
            );
        }

        $qualifyingCcmProblems = $qualifyingCcmProblemsCpmIdStack = $pcmProblems = $eligibleBhiProblemIds = [];

        if ( ! (is_array($problems) || is_a($problems, Collection::class))) {
            $problems = [$problems];
        }

        if ($problems) {
            foreach ($problems as $p) {
                if (empty($p)) {
                    continue;
                }

                if ( ! is_a($p, Problem::class)) {
                    $e = new \Exception('This is not an object of type '.Problem::class);
                    $this->setEligibilityJobStatusFromException($e);
                    $this->eligibilityJob->save();

                    return false;
                }

                $codeType    = null;
                $pcmProblems = [];

                if ($p->getCodeSystemName()) {
                    $codeType = getProblemCodeSystemName([$p->getCodeSystemName()]);
                }

                if ( ! $codeType) {
                    $codeType = 'all';
                }

                if ($p->getCode()) {
                    //Reject if patientData is on dialysis
                    //https://circlelinkhealth.atlassian.net/browse/CPM-954
                    if ('N18.6' == $p->getCode()) {
                        $this->ineligibleOnDialysis($eligibilityJobData);

                        return false;
                    }

                    if (in_array($codeType, [CpmConstants::ICD9_NAME, 'all'])) {
                        $cpmProblemId = $icd9Map->get($p->getCode());

                        if ($cpmProblemId && ! in_array($cpmProblemId, $qualifyingCcmProblemsCpmIdStack)) {
                            $qualifyingCcmProblems[]           = "{$cpmProblemsMap->get($cpmProblemId)}, ICD9: {$p->getCode()}";
                            $qualifyingCcmProblemsCpmIdStack[] = $cpmProblemId;

                            if ($allBhiProblemIds->contains($cpmProblemId)) {
                                $eligibleBhiProblemIds[] = $cpmProblemId;
                            }

                            continue;
                        }
                    }

                    if (in_array($codeType, [CpmConstants::ICD10_NAME, 'all'])) {
                        $cpmProblemId = $icd10Map->get($p->getCode());

                        if ($cpmProblemId && ! in_array($cpmProblemId, $qualifyingCcmProblemsCpmIdStack)) {
                            $qualifyingCcmProblems[]           = "{$cpmProblemsMap->get($cpmProblemId)}, ICD10: {$p->getCode()}";
                            $qualifyingCcmProblemsCpmIdStack[] = $cpmProblemId;

                            if ($allBhiProblemIds->contains($cpmProblemId)) {
                                $eligibleBhiProblemIds[] = $cpmProblemId;
                            }

                            continue;
                        }
                    }

                    if (in_array($codeType, [CpmConstants::SNOMED_NAME, 'all'])) {
                        $cpmProblemId = $snomedMap->get($p->getCode());

                        if ($cpmProblemId && ! in_array($cpmProblemId, $qualifyingCcmProblemsCpmIdStack)) {
                            $qualifyingCcmProblems[]           = "{$cpmProblemsMap->get($cpmProblemId)}, ICD10: {$p->getCode()}";
                            $qualifyingCcmProblemsCpmIdStack[] = $cpmProblemId;

                            if ($allBhiProblemIds->contains($cpmProblemId)) {
                                $eligibleBhiProblemIds[] = $cpmProblemId;
                            }

                            continue;
                        }
                    }

                    if ($this->practice->hasServiceCode('G2065')) {
                        $pcmProblemId = PcmProblem::where('practice_id', $this->practice->id)->where(
                            'code',
                            $p->getCode()
                        )->first();
                        if ($pcmProblemId) {
                            $pcmProblems[] = $pcmProblemId->id;
                        }
                    }
                }

                // Try to match keywords
                if ($p->getName()) {
                    if ($this->practice->hasServiceCode('G2065')) {
                        $pcmProblemId = PcmProblem::where('practice_id', $this->practice->id)->where(
                            'description',
                            $p->getName()
                        )->first();
                        if ($pcmProblemId) {
                            $pcmProblems[] = $pcmProblemId->id;
                        }
                    }
                    //Reject if patientData is on dialysis
                    //https://circlelinkhealth.atlassian.net/browse/CPM-954
                    if (Str::contains($p->getName(), ['End stage renal disease'])) {
                        $this->ineligibleOnDialysis($eligibilityJobData);

                        return false;
                    }

                    foreach ($cpmProblems->whereNotIn('id', $qualifyingCcmProblemsCpmIdStack) as $problem) {
                        foreach ($problem->searchKeywords as $keyword) {
                            if (empty($keyword)) {
                                continue;
                            }

                            if (Str::contains(strtolower($p->getName()), strtolower($keyword))
                                && ! in_array($problem->id, $qualifyingCcmProblemsCpmIdStack)
                            ) {
                                $code = SnomedToCpmIcdMap::where('icd_9_code', '!=', '')
                                    ->whereCpmProblemId($problem->id)
                                    ->get()
                                    ->sortByDesc('icd_9_avg_usage')
                                    ->first();

                                if ($code) {
                                    if ($code->icd_9_code) {
                                        $code = "ICD9: {$code->icd_9_code}";
                                    }
                                }

                                if ( ! $code) {
                                    $code = SnomedToCpmIcdMap::where('icd_10_code', '!=', '')
                                        ->whereCpmProblemId($problem->id)
                                        ->first();

                                    if ($code) {
                                        $code = "ICD10: {$code->icd_10_code}";
                                    }
                                }

                                $qualifyingCcmProblems[]           = "{$problem->name}, ${code}";
                                $qualifyingCcmProblemsCpmIdStack[] = $problem->id;

                                if ((bool) $problem->is_behavioral) {
                                    $eligibleBhiProblemIds[] = $problem->id;
                                }
                            }
                        }
                    }
                }
            }
        }

        $qualifyingCcmProblems = array_unique($qualifyingCcmProblems);
        $qualifyingBhiProblems = array_unique($eligibleBhiProblemIds);

        $ccmProbCount = count($qualifyingCcmProblems);
        $bhiProbCount = count($qualifyingBhiProblems);
        $pcmProbCount = count($pcmProblems);

        if ($this->eligibilityJob) {
            $this->eligibilityJob->bhi_problem_id   = $qualifyingBhiProblems[0] ?? null;
            $this->eligibilityJob->ccm_problem_1_id = $qualifyingCcmProblemsCpmIdStack[0] ?? null;
            $this->eligibilityJob->ccm_problem_2_id = $qualifyingCcmProblemsCpmIdStack[1] ?? null;

            if ( ! empty($pcmProblems = array_unique(array_filter($pcmProblems)))) {
                $eligibilityJobData['chargeable_services']['G2065']['problems'] = $pcmProblems;
            }
        }

        if ($ccmProbCount < 2 && 0 === $bhiProbCount && 0 === $pcmProbCount) {
            $this->setEligibilityJobStatus(
                3,
                [
                    'problems'              => 'Patient has less than 2 CCM conditions, and 0 BHI or PCM conditions',
                    'qualifyingCcmProblems' => $qualifyingCcmProblems,
                    'qualifyingBhiProblems' => $qualifyingBhiProblems,
                ],
                EligibilityJob::INELIGIBLE,
                'problems'
            );

            return false;
        }

        if ($ccmProbCount < 2 && $bhiProbCount > 0) {
            if ( ! $this->practice->hasServiceCode(
                'CPT 99484'
            ) && (0 === $pcmProbCount || ! $this->practice->hasServiceCode('G2065'))) {
                $this->setEligibilityJobStatus(
                    3,
                    ['problems' => 'Patient is BHI eligible, but practice does not support BHI. Patient has less than 2 ccm conditions.'],
                    EligibilityJob::INELIGIBLE,
                    'problems'
                );

                return false;
            }
        }

        if ($ccmProbCount < 2 && (0 === $bhiProbCount || ! $this->practice->hasServiceCode(
            'CPT 99484'
        )) && (0 === $pcmProbCount || ! $this->practice->hasServiceCode('G2065'))) {
            return false;
        }

        $eligibilityJobData['ccm_condition_1'] = $qualifyingCcmProblems[0] ?? null;
        $eligibilityJobData['ccm_condition_2'] = $qualifyingCcmProblems[1] ?? null;

        $eligibilityJobData['cpm_problem_1'] = $qualifyingCcmProblemsCpmIdStack[0] ?? null;
        $eligibilityJobData['cpm_problem_2'] = $qualifyingCcmProblemsCpmIdStack[1] ?? null;

        $this->eligibilityJob->data = $eligibilityJobData;

        return true;
    }

    /**
     * @throws \Exception
     *
     * @return $this
     */
    protected function validateStructureAndData()
    {
        $invalidStructure = false;
        $jsonErrors       = '';

        if (EligibilityBatch::TYPE_ONE_CSV == $this->batch->type && $this->eligibilityJob) {
            $structureErrors = $this->getCsvStructureErrors($this->eligibilityJob);
            if ( ! empty($structureErrors)) {
                $invalidStructure = true;
                $errors           = json_encode($structureErrors);
            }
        }

        if ((EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE == $this->batch->type) && $this->eligibilityJob) {
            $errors          = [];
            $structureErrors = $this->validateJsonStructure($this->eligibilityJob->data)->errors();
            if ($structureErrors->isNotEmpty()) {
                $errors[]         = 'structure';
                $invalidStructure = true;
            }
            $errors = array_merge($this->validateRow($this->eligibilityJob->data)->errors()->keys(), $errors);
            $this->saveErrorsOnEligibilityJob($this->eligibilityJob, collect($errors));
            $jsonErrors = $structureErrors->toJson();
        }

        if ($invalidStructure) {
            //if there are structure errors we stop the process because create enrollees fails from missing arguements
            $e = new InvalidStructureException(
                "Record with eligibility job id: {$this->eligibilityJob->id} has invalid structure. Errors:".$jsonErrors,
                422
            );

            $this->setEligibilityJobStatusFromException($e);
        }
    }

    private function adaptClhFormatInsurancePlansToPrimaryAndSecondary($record)
    {
        return (new JsonMedicalRecordInsurancePlansAdapter())->adapt($record);
    }

    /**
     * @param $patient
     *
     * @throws \Exception
     */
    private function createEnrollee(): bool
    {
        $args = $this->eligibilityJob->data;

        if ($args instanceof Collection) {
            $args = $args->all();
        }

        if ( ! is_array($args)) {
            throw new \Exception('$args is expected to be an array. '.EligibilityJob::class.':'.$this->eligibilityJob->id);
        }

        $args['primary_insurance']   = $this->eligibilityJob->data['primary_insurance'] ?? null;
        $args['secondary_insurance'] = $this->eligibilityJob->data['secondary_insurance'] ?? null;
        $args['tertiary_insurance']  = $this->eligibilityJob->data['tertiary_insurance'] ?? null;

        if (isset($args['insurance_plans']) || isset($args['insurance_plan'])) {
            $args = $this->adaptClhFormatInsurancePlansToPrimaryAndSecondary($args);
        }

        if (array_key_exists('preferred_provider', $args)) {
            $args['referring_provider_name'] = $args['preferred_provider'];
        }

        if (array_key_exists('language', $args)) {
            $args['lang'] = $args['language'];
        }

        $args['status'] = Enrollee::ELIGIBLE;

//            if (isset($args['cell_phone'])) {
//                $args['status'] = Enrollee::TO_SMS;
//            }

        $args['home_phone'] = isset($args['home_phone'])
            ? (new StringManipulation())->formatPhoneNumberE164($args['home_phone'])
            : null;
        $args['primary_phone'] = isset($args['primary_phone'])
            ? (new StringManipulation())->formatPhoneNumberE164($args['primary_phone'])
            : null;
        $args['cell_phone'] = isset($args['cell_phone'])
            ? (new StringManipulation())->formatPhoneNumberE164($args['cell_phone'])
            : null;
        $args['other_phone'] = isset($args['other_phone'])
            ? (new StringManipulation())->formatPhoneNumberE164($args['other_phone'])
            : null;

        if (array_key_exists('id', $args)) {
            unset($args['id']);
        }

        if ($this->eligibilityJob) {
            $args['eligibility_job_id'] = $this->eligibilityJob->id;
        }

        if (array_key_exists('postal_code', $args) && ! array_key_exists('zip', $args)) {
            $args['zip'] = $args['postal_code'];
        }

        if (array_key_exists('problems_string', $args)) {
            $args['problems'] = $args['problems_string'];
        }

        if (is_array($args['problems'])) {
            $args['problems'] = json_encode($args['problems']);
        }

        if (array_key_exists('insurances', $args) && ! (array_key_exists(
            'primary_insurance',
            $args
        ) && ! empty($args['primary_insurance']))) {
            $insurances = is_array($args['insurances'])
                ? collect($args['insurances'])
                : $args['insurances'];

            if (array_key_exists(0, $insurances) && array_keys_exist(
                ['insurancetype', 'insuranceplanname'],
                $insurances[0]
            )) {
                //Athena
                $args['primary_insurance']   = $insurances[0]['insuranceplanname'].'('.$insurances[0]['insurancetype'].')' ?? '';
                $args['secondary_insurance'] = $insurances[1]['insuranceplanname'].'('.$insurances[1]['insurancetype'].')' ?? '';
                $args['tertiary_insurance']  = $insurances[2]['insuranceplanname'].'('.$insurances[2]['insurancetype'].')' ?? '';
            } else {
                $args['primary_insurance']   = $insurances[0]['type'] ?? '';
                $args['secondary_insurance'] = $insurances[1]['type'] ?? '';
                $args['tertiary_insurance']  = $insurances[2]['type'] ?? '';
            }
        }

        $args['practice_id'] = $this->practice->id;
        $args['provider_id'] = $this->practice->user_id;

        if (empty($args['email'])) {
            $timestamp     = now()->timestamp;
            $args['email'] = "eJ_{$this->eligibilityJob->id}_$timestamp@cpm.com";
        }

        $args['address']   = $args['street'] ?? $args['address_line_1'] ?? '';
        $args['address_2'] = $args['street2'] ?? $args['address_line_2'] ?? '';

        $lastEncounter = $args['last_encounter'] ?? $args['last_visit'] ?? null;

        if ($lastEncounter) {
            $validator = Validator::make(
                [
                    'last_encounter' => $lastEncounter,
                ],
                [
                    'last_encounter' => 'required|filled|date',
                ]
            );

            if ($validator->fails()) {
                $args['last_encounter'] = null;
            } else {
                $args['last_encounter'] = Carbon::parse($lastEncounter);
            }
        }

        $args['batch_id'] = $this->batch->id;

        if (empty($args['mrn']) && array_key_exists('mrn_number', $args) && ! empty($args['mrn_number'])) {
            $args['mrn'] = $args['mrn_number'];
        } elseif (empty($args['mrn']) && array_key_exists('patient_id', $args) && ! empty($args['patient_id'])) {
            $args['mrn'] = $args['patient_id'];
        } elseif (empty($args['mrn']) && array_key_exists('internal_id', $args) && ! empty($args['internal_id'])) {
            $args['mrn'] = $args['internal_id'];
        }

        $args['dob'] = $args['dob'] ?? $args['date_of_birth'] ?? $args['birth_date'];

        if ( ! $args['dob'] instanceof Carbon) {
            $args['dob'] = Carbon::parse($args['dob']);
        }

        $enrolleeExists = Enrollee::where(
            [
                [
                    'practice_id',
                    '=',
                    $args['practice_id'],
                ],
                [
                    'first_name',
                    '=',
                    $args['first_name'],
                ],
                [
                    'last_name',
                    '=',
                    $args['last_name'],
                ],
                [
                    'dob',
                    '=',
                    $args['dob'],
                ],
            ]
        )->orWhere(
            [
                [
                    'practice_id',
                    '=',
                    $args['practice_id'],
                ],
                [
                    'mrn',
                    '=',
                    $args['mrn'],
                ],
            ]
        )->first();

        $enrolledPatientExists = User::withTrashed()
            ->where(
                function ($u) use ($args) {
                    $u->whereProgramId($args['practice_id'])
                        ->whereHas(
                            'patientInfo',
                            function ($q) use ($args) {
                                $q->withTrashed()->whereMrnNumber($args['mrn']);
                            }
                        );
                }
            )->orWhere(
                function ($u) use ($args) {
                    $u->where(
                        [
                            [
                                'program_id',
                                '=',
                                $args['practice_id'],
                            ],
                            [
                                'first_name',
                                '=',
                                $args['first_name'],
                            ],
                            [
                                'last_name',
                                '=',
                                $args['last_name'],
                            ],
                        ]
                    )->whereHas(
                        'patientInfo',
                        function ($q) use ($args) {
                            $q->withTrashed()->whereBirthDate($args['dob']);
                        }
                    );
                }
            )->first();

        $duplicateMySqlError = false;
        $errorMsg            = null;

        if ($enrolledPatientExists) {
            $this->setEligibilityJobStatus(
                3,
                [
                    'duplicate' => 'This patient already has a careplan. '.route(
                        'patient.careplan.print',
                        [$enrolledPatientExists->id]
                    ),
                ],
                EligibilityJob::ENROLLED
            );

            return false;
        }
        if ($enrolleeExists && optional($this->batch)->shouldSafeReprocess()) {
            $updated        = $enrolleeExists->update($args);
            $this->enrollee = $enrolleeExists->fresh();

            return true;
        }

        try {
            $this->enrollee = Enrollee::create($args);
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if (1062 == $errorCode) {
                $duplicateMySqlError = true;
                $errorMsg            = $e->getMessage();
            } else {
                throw $e;
            }
        }

        if ($enrolleeExists && $enrolleeExists->batch_id !== $this->batch->id) {
            $batchInfo = $enrolleeExists->batch_id
                ? " in batch {$enrolleeExists->batch_id}"
                : '';
            $this->setEligibilityJobStatus(
                3,
                ['eligible-also-in-previous-batch' => "This patient has already been processed and found to be eligible${batchInfo}. Eligible Patient Id: {$enrolleeExists->id}"],
                EligibilityJob::ELIGIBLE_ALSO_IN_PREVIOUS_BATCH
            );

            return false;
        }

//        Jobs may be picked up twice for processing
//        if ($duplicateMySqlError) {
//            $this->setEligibilityJobStatus(
//                3,
//                ['duplicate' => "Seems like the Enrollee already exists. Error caused: ${errorMsg}."],
//                EligibilityJob::DUPLICATE
//            );
//
//            return true;
//        }

        $this->setEligibilityJobStatus(3, [], EligibilityJob::ELIGIBLE);

        return true;
    }

    private function getSnomedToIcdMap()
    {
        return \Cache::remember(
            'all_snomed_to_cpm_icd_maps',
            5,
            function () {
                return SnomedToCpmIcdMap::all();
            }
        );
    }

    /**
     * Set a patient's status as ineligible because they are on dialysis.
     * If the patient is on dialysis, they are ineligible.
     *
     * @param $row
     */
    private function ineligibleOnDialysis($row)
    {
        $this->setEligibilityJobStatus(
            3,
            ['problems' => 'Patient is on dialysis. Patient has code N18.6'],
            EligibilityJob::INELIGIBLE,
            'on_dialysis'
        );
    }

    /**
     * @param array $messages
     * @param null  $outcome
     * @param null  $reason
     */
    private function setEligibilityJobStatus(int $status, $messages = [], $outcome = null, $reason = null)
    {
        if ( ! $this->eligibilityJob) {
            return;
        }

        $this->eligibilityJob->status   = $status;
        $this->eligibilityJob->messages = $messages;
        $this->eligibilityJob->outcome  = $outcome;
        $this->eligibilityJob->reason   = $reason;
    }

    private function setEligibilityJobStatusFromException(\Exception $e)
    {
        switch ((int) $e->getCode()) {
            case 422:
                $reason = 'invalid data';
                break;
            case 500:
                $reason = 'possible bug';
                break;
            default:
                $reason = null;
        }

        $this->setEligibilityJobStatus(
            2,
            [
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ],
            EligibilityJob::ERROR,
            $reason
        );
    }

    private function validateInsuranceWithCollection($record)
    {
        $isEligible = false;

        $i = 0;

        foreach ($record['insurances'] as $insurance) {
            if (array_key_exists('type', $insurance)) {
                if ($this->eligibilityJob && ! empty($insurance) && $i < 3) {
                    switch ($i) {
                        case 0:
                            $this->eligibilityJob->primary_insurance = $insurance['type'];

                            if (Str::contains(strtolower($insurance['type']), 'medicare')
                            ) {
                                $isEligible = true;
                            }

                            break;
                        case 1:
                            $this->eligibilityJob->secondary_insurance = $insurance['type'];

                            if (Str::contains(strtolower($insurance['type']), 'medicare')
                            ) {
                                $isEligible = true;
                            }

                            break;
                        case 2:
                            $this->eligibilityJob->tertiary_insurance = $insurance['type'];

                            if (Str::contains(strtolower($insurance['type']), 'medicare')
                            ) {
                                $isEligible = true;
                            }

                            break;
                        default:
                            break;
                    }

                    ++$i;
                }
            } elseif (array_keys_exist(['insurancetype', 'insuranceplanname'], $insurance)) {
                //Athena

                if (0 === $i) {
                    $this->eligibilityJob->primary_insurance = $insurance['insuranceplanname'].'('.$insurance['insurancetype'].')' ?? '';

                    if (Str::contains(strtolower($this->eligibilityJob->primary_insurance), 'medicare')
                    ) {
                        $isEligible = true;
                    }
                }
                if (1 === $i) {
                    $this->eligibilityJob->secondary_insurance = $insurance['insuranceplanname'].'('.$insurance['insurancetype'].')' ?? '';

                    if (Str::contains(strtolower($this->eligibilityJob->secondary_insurance), 'medicare')
                    ) {
                        $isEligible = true;
                    }
                }
                if (2 === $i) {
                    $this->eligibilityJob->tertiary_insurance = $insurance['insuranceplanname'].'('.$insurance['insurancetype'].')' ?? '';

                    if (Str::contains(strtolower($this->eligibilityJob->tertiary_insurance), 'medicare')
                    ) {
                        $isEligible = true;
                    }
                }

                ++$i;
            }
        }

        if ( ! $isEligible) {
            $this->setEligibilityJobStatus(
                3,
                ['insurance' => 'No medicare found'],
                EligibilityJob::INELIGIBLE,
                'insurance'
            );

            return false;
        }

        return true;
    }

    private function validateInsuranceWithPrimarySecondaryTertiary($record)
    {
        $primary   = strtolower($record['primary_insurance'] ?? null);
        $secondary = strtolower($record['secondary_insurance'] ?? null);
        $tertiary  = strtolower($record['tertiary_insurance'] ?? null);

        //Change none to an empty string
        if (Str::contains($primary, 'none') || empty($primary)) {
            $primary = null;
        }
        if (Str::contains($secondary, ['none', 'no secondary plan']) || empty($secondary)) {
            $secondary = null;
        }
        if (Str::contains($tertiary, ['none', 'no tertiary plan']) || empty($tertiary)) {
            $tertiary = null;
        }

        $isEligible = false;

        if (Str::contains(strtolower($primary), 'medicare')) {
            $isEligible = true;
        }

        if ($this->eligibilityJob) {
            $this->eligibilityJob->primary_insurance   = $primary;
            $this->eligibilityJob->secondary_insurance = $secondary;
            $this->eligibilityJob->tertiary_insurance  = $tertiary;
        }

        if ( ! $isEligible) {
            $this->setEligibilityJobStatus(
                3,
                ['insurance' => 'No medicare found'],
                EligibilityJob::INELIGIBLE,
                'insurance'
            );

            return false;
        }

        return true;
    }
}
