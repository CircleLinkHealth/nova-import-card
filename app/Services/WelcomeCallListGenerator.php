<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\CLH\CCD\Importer\SnomedToCpmIcdMap;
use App\Constants;
use App\EligibilityBatch;
use App\EligibilityJob;
use App\Enrollee;
use App\Models\CPM\CpmProblem;
use App\Practice;
use App\Services\Eligibility\Adapters\JsonMedicalRecordInsurancePlansAdapter;
use App\Services\Eligibility\Csv\CsvPatientList;
use App\Services\Eligibility\Entities\Problem;
use App\Traits\ValidatesEligibility;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

/**
 * @property null medicalRecordType
 */
class WelcomeCallListGenerator
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
     * An array representation of the Ineligible. This will normally be uploaded as a csv file.
     *
     * @var Collection
     */
    public $ineligiblePatients;
    /**
     * An array representation of the Patient List. This will normally be uploaded as a csv file.
     *
     * @var Collection
     */
    public $patientList;

    protected $invalidStructure = false;
    /**
     * @var null
     */
    private $batch;
    /**
     * @var EligibilityJob
     */
    private $eligibilityJob;

    /**
     * WelcomeCallListGenerator constructor.
     *
     * @param Collection          $patientList
     * @param bool                $filterLastEncounter
     * @param bool                $filterInsurance
     * @param bool                $filterProblems
     * @param bool                $createEnrollees
     * @param Practice|null       $practice
     * @param null                $medicalRecordType
     * @param null                $medicalRecordId
     * @param EligibilityBatch    $batch
     * @param EligibilityJob|null $eligibilityJob
     *
     * @throws \Exception
     */
    public function __construct(
        Collection $patientList,
        $filterLastEncounter = true,
        $filterInsurance = true,
        $filterProblems = true,
        $createEnrollees = true,
        Practice $practice = null,
        $medicalRecordType = null,
        $medicalRecordId = null,
        EligibilityBatch $batch = null,
        EligibilityJob $eligibilityJob = null
    ) {
        $this->patientList        = $patientList;
        $this->ineligiblePatients = new Collection();

        $this->filterLastEncounter = $filterLastEncounter;
        $this->filterInsurance     = $filterInsurance;
        $this->filterProblems      = $filterProblems;
        $this->createEnrollees     = $createEnrollees;
        $this->practice            = $practice;
        $this->medicalRecordType   = $medicalRecordType;
        $this->medicalRecordId     = $medicalRecordId;
        $this->batch               = $batch;

        $this->eligibilityJob = $eligibilityJob;

        if ($this->eligibilityJob) {
            $this->eligibilityJob->status = 1;
            $this->eligibilityJob->save();
        }

        try {
            $this->filterPatientList();

            $this->createEnrollees();
        } catch (\Exception $e) {
            if ($this->eligibilityJob) {
                $this->setEligibilityJobStatus(2, [
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]);

                $this->eligibilityJob->save();
            }

            throw $e;
        }
    }

    public function __destruct()
    {
        if ($this->batch) {
            $this->batch->save();
        }

        if ($this->eligibilityJob) {
            try {
                $this->eligibilityJob->save();
            } catch (\Exception $e) {
                \Log::critical($e);
                \Log::critical($this->eligibilityJob);

                throw new \Exception("Eligibility processing exception: {$e->getMessage()}");
            }
        }
    }

    /**
     * Create PreEnrollees from the filtered patientList.
     *
     * @return $this
     */
    public function createEnrollees()
    {
        if ( ! $this->createEnrollees) {
            return $this;
        }

        $this->patientList->reject(function ($patient) {
            $args = $patient;

            if (is_a($args, Collection::class)) {
                $args = $args->all();
            }

            if (isset($args['insurance_plans']) || isset($args['insurance_plan'])) {
                $args = $this->adaptClhFormatInsurancePlansToPrimaryAndSecondary($args);
            }

            if (array_key_exists('preferred_provider', $args)) {
                $args['referring_provider_name'] = $args['preferred_provider'];
            }

            if (array_key_exists('language', $args)) {
                $args['lang'] = $args['language'];
            }

//            $args['status'] = Enrollee::TO_CALL;

//            if (isset($args['cell_phone'])) {
//                $args['status'] = Enrollee::TO_SMS;
//            }

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

            if (array_key_exists('insurances', $args) && ! array_key_exists('primary_insurance', $args)) {
                $insurances = is_array($args['insurances'])
                    ? collect($args['insurances'])
                    : $args['insurances'];

                $args['primary_insurance'] = $insurances[0]['type'] ?? '';
                $args['secondary_insurance'] = $insurances[1]['type'] ?? '';
                $args['tertiary_insurance'] = $insurances[2]['type'] ?? '';
            }

            $args['practice_id'] = $this->practice->id;
            $args['provider_id'] = $this->practice->user_id;

            if (is_a($args, Collection::class)) {
                $args = $args->all();
            }

            if (empty($args['email'])) {
                $args['email'] = 'noEmail@noEmail.com';
            }

            $args['address'] = $args['street'] ?? $args['address_line_1'] ?? '';
            $args['address_2'] = $args['street2'] ?? $args['address_line_2'] ?? '';

            $args['medical_record_type'] = $this->medicalRecordType;
            $args['medical_record_id'] = $this->medicalRecordId;

            $lastEncounter = $args['last_encounter'] ?? $args['last_visit'] ?? null;

            if ($lastEncounter) {
                $validator = Validator::make([
                    'last_encounter' => $lastEncounter,
                ], [
                    'last_encounter' => 'required|filled|date',
                ]);

                if ($validator->fails()) {
                    $args['last_encounter'] = null;
                } else {
                    $args['last_encounter'] = Carbon::parse($lastEncounter);
                }
            }

            $args['batch_id'] = $this->batch->id;
            $args['mrn'] = $args['mrn'] ?? $args['mrn_number'] ?? $args['patient_id'];

            $args['dob'] = $args['dob'] ?? $args['date_of_birth'] ?? $args['birth_date'];

            $enrolleeExists = Enrollee::where([
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
            ])->orWhere([
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
            ])->first();

            $enrolledPatientExists = User::withTrashed()
                ->where(function ($u) use ($args) {
                    $u->whereProgramId($args['practice_id'])
                        ->whereHas('patientInfo', function ($q) use ($args) {
                            $q->withTrashed()->whereMrnNumber($args['mrn']);
                        });
                })->orWhere(function ($u) use ($args) {
                    $u->where([
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
                    ])->whereHas('patientInfo', function ($q) use ($args) {
                        $q->withTrashed()->whereBirthDate($args['dob']);
                    });
                })->first();

            $duplicateMySqlError = false;
            $errorMsg = null;

            if ($enrolledPatientExists) {
                $this->setEligibilityJobStatus(3, [
                    'duplicate' => 'This patient already has a careplan. '.route(
                        'patient.careplan.print',
                            [$enrolledPatientExists->id]
                    ),
                ], EligibilityJob::ENROLLED);

                return true;
            }
            if ($enrolleeExists && optional($this->batch)->shouldSafeReprocess()) {
                $updated = $enrolleeExists->update($args);
                $this->enrollees = $enrolleeExists->fresh();

                return false;
            }

            try {
                $this->enrollees = Enrollee::create($args);
            } catch (\Illuminate\Database\QueryException $e) {
                $errorCode = $e->errorInfo[1];
                if (1062 == $errorCode) {
                    $duplicateMySqlError = true;
                    $errorMsg = $e->getMessage();
                } else {
                    throw $e;
                }
            }

            if ($enrolleeExists) {
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

            if ($duplicateMySqlError) {
                $this->setEligibilityJobStatus(
                    3,
                    ['duplicate' => "Seems like the Enrollee already exists. Error caused: ${errorMsg}."],
                    EligibilityJob::DUPLICATE
                );

                return true;
            }
            $this->setEligibilityJobStatus(3, [], EligibilityJob::ELIGIBLE);

            return false;
        });
    }

    /**
     * Exports the Ineligible Patient List to a csv file.
     */
    public function exportIneligibleToCsv()
    {
        $now = Carbon::now()->toDateTimeString();

        $this->ineligiblePatients = $this->ineligiblePatients->map(function ($patient) {
            $requiredKeys = [
                'patient_id',
                'email',
                'first_name',
                'last_name',
                'home_phone',
                'primary_phone',
                'work_phone',
                'preferred_contact_method',
                'preferred_provider',
                'address_1',
                'address_2',
                'city',
                'state',
                'zip',
                'patient_name',
                'last_encounter',
                'allergy',
                'primary_insurance',
                'secondary_insurance',
                'provider',
                'county',
                'medications',
                'problems',
                'ccm_condition_1',
                'ccm_condition_2',
                'cpm_problem_1',
                'cpm_problem_2',
            ];

            $keys = $patient->keys();

            foreach ($requiredKeys as $k) {
                if ( ! $keys->contains($k)) {
                    $patient->put($k, '');
                }
            }

            $patientArr = $patient->all();

            ksort($patientArr);

            return $patientArr;
        });

        Excel::create("Ineligible Patients Welcome Call List - ${now}", function ($excel) {
            $excel->sheet('Ineligible', function ($sheet) {
                $sheet->fromArray(
                    $this->ineligiblePatients->values()->all()
                );
            });
        })->export('xls');
    }

    /**
     * Exports the Patient List to a csv file.
     *
     * @param mixed      $download
     * @param mixed      $storeOnServer
     * @param mixed|null $filenamePrefix
     * @param mixed      $returnStorageInfo
     */
    public function exportToCsv(
        $download = true,
        $storeOnServer = false,
        $filenamePrefix = null,
        $returnStorageInfo = false
    ) {
        $filename = 'Welcome Call List';

        if ($filenamePrefix) {
            $filename = "${filenamePrefix} ${filename}";
        }

        $now = Carbon::now()->toDateTimeString();

        $this->patientList = $this->patientList->map(function ($patient) {
            $requiredKeys = [
                'patient_id',
                'email',
                'first_name',
                'last_name',
                'home_phone',
                'primary_phone',
                'work_phone',
                'preferred_contact_method',
                'preferred_provider',
                'address_1',
                'address_2',
                'city',
                'state',
                'zip',
                'patient_name',
                'last_encounter',
                'allergy',
                'primary_insurance',
                'secondary_insurance',
                'provider',
                'county',
                'medications',
                'problems',
                'ccm_condition_1',
                'ccm_condition_2',
                'cpm_problem_1',
                'cpm_problem_2',
            ];

            $keys = $patient->keys();

            foreach ($requiredKeys as $k) {
                if ( ! $keys->contains($k)) {
                    $patient->put($k, '');
                }
            }

            $patientArr = $patient->all();

            ksort($patientArr);

            return $patientArr;
        });

        $slug = str_slug("${filename} - ${now}", '_');

        $excel = Excel::create($slug, function ($excel) {
            $excel->sheet('Welcome Calls', function ($sheet) {
                $sheet->fromArray(
                    $this->patientList->values()->all()
                );
            });
        });

        if ($storeOnServer) {
            if ( ! $returnStorageInfo) {
                $excel->store('xls', false, false);
            } else {
                return $excel->store('xls', false, true);
            }
        }

        if ($download) {
            return $excel->export('xls');
        }
    }

    public function getEligibilityJob()
    {
        return $this->eligibilityJob;
    }

    /**
     * @return Collection
     */
    public function getPatientList(): Collection
    {
        return $this->patientList;
    }

    protected function byInsurance(): WelcomeCallListGenerator
    {
        if ( ! $this->filterInsurance) {
            return $this;
        }

        $this->patientList = $this->patientList->reject(function (&$record) {
            if (isset($record['insurance_plans']) || isset($record['insurance_plan'])) {
                //adapt record so that we can use validateInsuranceWithPrimarySecondaryTertiary()
                $record = $this->adaptClhFormatInsurancePlansToPrimaryAndSecondary($record);
            }

            if (isset($record['insurances'])) {
                return ! $this->validateInsuranceWithCollection($record);
            }
            if (isset($record['primary_insurance']) || isset($record['secondary_insurance']) || isset($record['tertiary_insurance'])) {
                return ! $this->validateInsuranceWithPrimarySecondaryTertiary($record);
            }
            $this->ineligiblePatients->push($record);

            $this->setEligibilityJobStatus(
                    3,
                    ['insurance' => 'No insurance plans found.'],
                    EligibilityJob::INELIGIBLE,
                    'insurance'
                );

            return true;
        });

        return $this;
    }

    /**
     * Removes Patients whose last encounter was before Feb. 1st, 2016 from the list.
     *
     * @return WelcomeCallListGenerator
     */
    protected function byLastEncounter(): WelcomeCallListGenerator
    {
        if ( ! $this->filterLastEncounter) {
            return $this;
        }

        $this->patientList = $this->patientList->reject(function ($row) {
            //Anything past this date is valid
            $minEligibleDate = Carbon::now()->subYear();

            $possibleNames = [
                'last_encounter',
                'last_visit',
            ];

            foreach ($possibleNames as $n) {
                if (isset($row[$n])) {
                    $lastEncounter = $row[$n];
                }
            }

            //If last encounter is not set, the check is skipped
            if ( ! isset($lastEncounter)) {
//                $this->ineligiblePatients->push($row);

//                $this->setEligibilityJobStatus(3, ['last_encounter' => 'No last encounter field found'],
//                    EligibilityJob::INELIGIBLE);

                return false;
            }

            if ('null' == strtolower($lastEncounter)) {
                return false;
            }

            $validator = Validator::make([
                'last_encounter' => $lastEncounter,
            ], [
                'last_encounter' => 'required|filled|date',
            ]);

            if ($validator->fails()) {
                $this->ineligiblePatients->push($row);

                $this->setEligibilityJobStatus(3, [
                    'last_encounter' => implode(',', $validator->messages()->all())." value: `${lastEncounter}`",
                ], EligibilityJob::INELIGIBLE, 'last_encounter');

                return true;
            }

            $lastEncounterDate = is_a($lastEncounter, Carbon::class)
                ? $lastEncounter
                : new Carbon($lastEncounter);

            if ($this->eligibilityJob) {
                $this->eligibilityJob->last_encounter = $lastEncounterDate;
            }

            if ($lastEncounterDate->lt($minEligibleDate)) {
                $this->ineligiblePatients->push($row);

                $this->setEligibilityJobStatus(
                    3,
                    ['last_encounter' => "Patient last encounter `{$lastEncounterDate->toDateString()}` is more than a year ago."],
                    EligibilityJob::INELIGIBLE,
                    'last_encounter'
                );

                return true;
            }

            return false;
        });

        return $this;
    }

    protected function byNumberOfProblems(): WelcomeCallListGenerator
    {
        if ( ! $this->filterProblems || $this->patientList->isEmpty()) {
            return $this;
        }

        $cpmProblems = \Cache::remember('all_cpm_problems', 60, function () {
            return CpmProblem::all()
                ->transform(function ($problem) {
                    $problem->searchKeywords = collect(explode(',', $problem->contains), [$problem->name])
                        ->transform(function ($keyword) {
                            return trim(strtolower($keyword));
                        })
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();

                    return $problem;
                });
        });

        $icd9Map = \Cache::remember('map_icd_9_to_cpm_problems', 60, function () {
            $snomedToIcdMap = $this->getSnomedToIcdMap();

            return $snomedToIcdMap->pluck('cpm_problem_id', Constants::ICD9);
        });

        $icd10Map = \Cache::remember('map_icd_10_to_cpm_problems', 60, function () {
            $snomedToIcdMap = $this->getSnomedToIcdMap();

            return $snomedToIcdMap->pluck('cpm_problem_id', Constants::ICD10);
        });

        $snomedMap = \Cache::remember('map_snomed_to_cpm_problems', 60, function () {
            $snomedToIcdMap = $this->getSnomedToIcdMap();

            return $snomedToIcdMap->pluck('cpm_problem_id', Constants::SNOMED);
        });

        $cpmProblemsMap = \Cache::remember('map_name_to_cpm_problems', 60, function () use ($cpmProblems) {
            return $cpmProblems->pluck('name', 'id');
        });

        $allBhiProblemIds = \Cache::remember('bhi_cpm_problem_ids', 60, function () use ($cpmProblems) {
            return $cpmProblems->where('is_behavioral', '=', true)->pluck('id');
        });

        $this->patientList = $this->patientList->map(function ($row) use (
            $cpmProblems,
            $icd9Map,
            $icd10Map,
            $snomedMap,
            $cpmProblemsMap,
            $allBhiProblemIds
        ) {
            $row['ccm_condition_1'] = '';
            $row['ccm_condition_2'] = '';
            $row['cpm_problem_1'] = '';
            $row['cpm_problem_2'] = '';

            $problems = $row['problems'] ?? $row['problems_string'];

            if (empty($problems)) {
                $this->ineligiblePatients->push($row);

                $this->setEligibilityJobStatus(
                    3,
                    ['problems' => 'Patient has 0 conditions.'],
                    EligibilityJob::INELIGIBLE,
                    'problems'
                );

                return false;
            }

            foreach (config('importer.problem_loggers') as $class) {
                $class = app($class);

                if ($class->shouldHandle($problems)) {
                    $problems = $class->handle($problems);
                    break;
                }
            }

            $qualifyingCcmProblems = [];

            //the cpm_problem_id for qualifying problems
            $qualifyingCcmProblemsCpmIdStack = [];

            $eligibleBhiProblemIds = [];

            if ( ! (is_array($problems) || is_a($problems, Collection::class))) {
                $problems = [$problems];
            }

            if ($problems) {
                foreach ($problems as $p) {
                    if ( ! is_a($p, Problem::class)) {
                        throw new \Exception('This is not an object of type '.Problem::class);
                    }

                    $codeType = null;

                    if ($p->getCodeSystemName()) {
                        $codeType = getProblemCodeSystemName([$p->getCodeSystemName()]);
                    }

                    if ( ! $codeType) {
                        $codeType = 'all';
                    }

                    if ($p->getCode()) {
                        if (in_array($codeType, [Constants::ICD9_NAME, 'all'])) {
                            $cpmProblemId = $icd9Map->get($p->getCode());

                            if ($cpmProblemId && ! in_array($cpmProblemId, $qualifyingCcmProblemsCpmIdStack)) {
                                $qualifyingCcmProblems[] = "{$cpmProblemsMap->get($cpmProblemId)}, ICD9: {$p->getCode()}";
                                $qualifyingCcmProblemsCpmIdStack[] = $cpmProblemId;

                                if ($allBhiProblemIds->contains($cpmProblemId)) {
                                    $eligibleBhiProblemIds[] = $cpmProblemId;
                                }

                                continue;
                            }
                        }

                        if (in_array($codeType, [Constants::ICD10_NAME, 'all'])) {
                            $cpmProblemId = $icd10Map->get($p->getCode());

                            if ($cpmProblemId && ! in_array($cpmProblemId, $qualifyingCcmProblemsCpmIdStack)) {
                                $qualifyingCcmProblems[] = "{$cpmProblemsMap->get($cpmProblemId)}, ICD10: {$p->getCode()}";
                                $qualifyingCcmProblemsCpmIdStack[] = $cpmProblemId;

                                if ($allBhiProblemIds->contains($cpmProblemId)) {
                                    $eligibleBhiProblemIds[] = $cpmProblemId;
                                }

                                continue;
                            }
                        }

                        if (in_array($codeType, [Constants::SNOMED_NAME, 'all'])) {
                            $cpmProblemId = $snomedMap->get($p->getCode());

                            if ($cpmProblemId && ! in_array($cpmProblemId, $qualifyingCcmProblemsCpmIdStack)) {
                                $qualifyingCcmProblems[] = "{$cpmProblemsMap->get($cpmProblemId)}, ICD10: {$p->getCode()}";
                                $qualifyingCcmProblemsCpmIdStack[] = $cpmProblemId;

                                if ($allBhiProblemIds->contains($cpmProblemId)) {
                                    $eligibleBhiProblemIds[] = $cpmProblemId;
                                }

                                continue;
                            }
                        }
                    }

                    // Try to match keywords
                    if ($p->getName()) {
                        foreach ($cpmProblems->whereNotIn('id', $qualifyingCcmProblemsCpmIdStack) as $problem) {
                            foreach ($problem->searchKeywords as $keyword) {
                                if (empty($keyword)) {
                                    continue;
                                }

                                if (str_contains(strtolower($p->getName()), strtolower($keyword))
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

                                    $qualifyingCcmProblems[] = "{$problem->name}, ${code}";
                                    $qualifyingCcmProblemsCpmIdStack[] = $problem->id;

                                    if ((bool) $problem->is_behavioral) {
                                        $eligibleBhiProblemIds[] = $problem->id;
                                    }
                                }
                            }
                        }
                    }

                    //Stop checking if we've already found 2 ccm problems
                    if (2 == count($qualifyingCcmProblems)) {
                        break;
                    }
                }
            }

            $qualifyingCcmProblems = array_unique($qualifyingCcmProblems);
            $qualifyingBhiProblems = array_unique($eligibleBhiProblemIds);

            $ccmProbCount = count($qualifyingCcmProblems);
            $bhiProbCount = count($qualifyingBhiProblems);

            if ($this->eligibilityJob) {
                $this->eligibilityJob->bhi_problem_id = $qualifyingBhiProblems[0] ?? null;
                $this->eligibilityJob->ccm_problem_1_id = $qualifyingCcmProblemsCpmIdStack[0] ?? null;
                $this->eligibilityJob->ccm_problem_2_id = $qualifyingCcmProblemsCpmIdStack[1] ?? null;
            }

            if ($ccmProbCount < 2 && 0 == $bhiProbCount) {
                $this->ineligiblePatients->push($row);

                $this->setEligibilityJobStatus(
                    3,
                    ['problems' => 'Patient has less than 2 ccm conditions'],
                    EligibilityJob::INELIGIBLE,
                    'problems'
                );

                return false;
            }

            if ($ccmProbCount < 2 && $bhiProbCount > 0) {
                if ( ! $this->practice->hasServiceCode('CPT 99484')) {
                    $this->ineligiblePatients->push($row);

                    $this->setEligibilityJobStatus(
                        3,
                        ['problems' => 'Patient is BHI eligible, but practice does not support BHI. Patient has less than 2 ccm conditions.'],
                        EligibilityJob::INELIGIBLE,
                        'problems'
                    );

                    return false;
                }
            }

            $row['ccm_condition_1'] = $qualifyingCcmProblems[0];
            $row['ccm_condition_2'] = $qualifyingCcmProblems[1] ?? null;

            $row['cpm_problem_1'] = $qualifyingCcmProblemsCpmIdStack[0];
            $row['cpm_problem_2'] = $qualifyingCcmProblemsCpmIdStack[1] ?? null;

            return $row;
        })->filter()->values();

        return $this;
    }

    protected function filterPatientList()
    {
        $this->validateStructureAndData()
            ->byNumberOfProblems()
            ->byLastEncounter()
            ->byInsurance();
    }

    protected function validateStructureAndData()
    {
        if (EligibilityBatch::TYPE_ONE_CSV == $this->batch->type && $this->eligibilityJob) {
            $csvPatientList = new CsvPatientList(collect($this->patientList));
            $isValid        = $csvPatientList->guessValidator() ?? null;

            $this->patientList->each(function ($patient) use ($isValid) {
                $errors = [];
                if ( ! $isValid) {
                    $errors[] = 'structure';
                    $this->invalidStructure = true;
                }
                $errors = array_merge($this->validateRow($patient)->errors()->keys(), $errors);
                $this->saveErrorsOnEligibilityJob($this->eligibilityJob, collect($errors));
            });
        }

        if (EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE == $this->batch->type && $this->eligibilityJob) {
            $this->patientList->each(function ($patient) {
                $errors = [];
                $structureErrors = $this->validateJsonStructure($patient)->errors();
                if ($structureErrors->isNotEmpty()) {
                    $errors[] = 'structure';
                    $this->invalidStructure = true;
                }
                $errors = array_merge($this->validateRow($patient)->errors()->keys(), $errors);
                $this->saveErrorsOnEligibilityJob($this->eligibilityJob, collect($errors));
            });
        }
        if ($this->invalidStructure) {
            //if there are structure errors we stop the process because create enrollees fails from missing arguements
            throw new \Exception(
                "Record with eligibility job id: {$this->eligibilityJob->id} has invalid structure.",
                422
            );
        }

        return $this;
    }

    private function adaptClhFormatInsurancePlansToPrimaryAndSecondary($record)
    {
        return (new JsonMedicalRecordInsurancePlansAdapter())->adapt($record);
    }

    private function getSnomedToIcdMap()
    {
        return \Cache::remember('all_snomed_to_cpm_icd_maps', 60, function () {
            return SnomedToCpmIcdMap::all();
        });
    }

    /**
     * @param int   $status
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

    private function validateInsuranceWithCollection($record)
    {
        $eligibleInsurances = [];

        $i = 0;

        foreach ($record['insurances'] as $insurance) {
            if (str_contains(strtolower($insurance['type']), [
                'medicare b',
                'medicare part b',
                'medicare',
            ])
            ) {
                $eligibleInsurances[] = $insurance['type'];
            }

            if ($this->eligibilityJob && ! empty($insurance) && $i < 3) {
                switch ($i) {
                    case 0:
                        $this->eligibilityJob->primary_insurance = $insurance['type'];
                        break;
                    case 1:
                        $this->eligibilityJob->secondary_insurance = $insurance['type'];
                        break;
                    case 2:
                        $this->eligibilityJob->tertiary_insurance = $insurance['type'];
                        break;
                    default:
                        break;
                }

                ++$i;
            }
        }

        if (count($eligibleInsurances) < 1) {
            $this->ineligiblePatients->push($record);

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
        if (str_contains($primary, 'none') || empty($primary)) {
            $primary = null;
        }
        if (str_contains($secondary, ['none', 'no secondary plan']) || empty($secondary)) {
            $secondary = null;
        }
        if (str_contains($tertiary, ['none', 'no tertiary plan']) || empty($tertiary)) {
            $tertiary = null;
        }

        //Keep the patient if they have medicaid
//            if (str_contains($primary, 'medicaid') || str_contains($secondary, 'medicaid')) {
//                return false;
//            }

        $eligibleInsurances = [];

        foreach ([$primary, $secondary, $tertiary] as $insurance) {
            if (str_contains(strtolower($insurance), [
                'medicare',
            ])
            ) {
                $eligibleInsurances[] = $insurance;
            }
        }

        if ($this->eligibilityJob) {
            $this->eligibilityJob->primary_insurance   = $primary;
            $this->eligibilityJob->secondary_insurance = $secondary;
            $this->eligibilityJob->tertiary_insurance  = $tertiary;
        }

        if (count($eligibleInsurances) < 1) {
            $this->ineligiblePatients->push($record);

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
