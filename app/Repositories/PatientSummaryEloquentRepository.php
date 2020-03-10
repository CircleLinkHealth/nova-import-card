<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use Cache;
use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Problem;
use Illuminate\Support\Collection;

class PatientSummaryEloquentRepository
{
    const MINUTES_20 = 1200;
    const MINUTES_40 = 2400;
    const MINUTES_60 = 3600;

    public $callRepo;
    public $patientRepo;

    public function __construct(PatientWriteRepository $patientRepo, CallRepository $callRepo)
    {
        $this->patientRepo = $patientRepo;
        $this->callRepo    = $callRepo;
    }

    public function approveIfShouldApprove(User $patient, PatientMonthlySummary $summary)
    {
        $isBHI = $summary->hasServiceCode('CPT 99484');

        if (( ! $this->lacksProblems($summary) && ! $isBHI) || ($isBHI && $summary->hasAtLeastOneBhiProblem())) {
            if ( ! $summary->approved && ! $summary->rejected && $this->shouldApprove($patient, $summary)) {
                $summary->approved = true;

                if ($summary->problem_1 && $summary->problem_2) {
                    Problem::whereNotIn(
                        'id',
                        array_filter([$summary->problem_1, $summary->problem_2])
                    )
                        ->update(
                            [
                                'billable' => false,
                            ]
                        );
                }

                Problem::whereIn('id', array_filter([$summary->problem_1, $summary->problem_2]))
                    ->update(
                        [
                            'billable' => true,
                        ]
                    );
            }
        } else {
            $summary->approved = false;
        }

        return $summary;
    }

    /**
     * Attach 2 billable problems to the given PatientMonthlySummary, and returns a new summary.
     * NOTE: The summary is not persisted to the DB. You will have to call `->save()` on `$summary` this function will
     * return.
     *
     * @return PatientMonthlySummary
     */
    public function attachBillableProblems(User $patient, PatientMonthlySummary $summary)
    {
        if ($this->shouldNotTouch($summary)) {
            return $summary;
        }

        $problemsDeleted = $this->removeDeletedConditions($summary);

        $skipValidation = false;
        if ( ! $this->hasBillableProblemsNameAndCode($summary)) {
            $summary = $this->fillBillableProblemsNameAndCode($summary);
        }

        if ( ! $this->shouldAttachProblems($patient, $summary)) {
            return $this->determineStatusAndSave($summary);
        }

        if ($this->lacksProblems($summary) && ! $problemsDeleted) {
            $this->fillProblemsUsingOlderSummary($summary);
        }

        if ($summary->hasServiceCode('CPT 99484') && ! $summary->hasAtLeastOneBhiProblem()) {
            $summary = $this->attachBhiProblem($summary);
        }

        if ($this->lacksProblems($summary)) {
            $summary = $this->TO_DEPRECATE_fillProblems(
                $patient,
                $summary,
                $patient->ccdProblems->where('billable', '=', true)->sortByDesc(
                    function ($ccdProblem) {
                        return optional($ccdProblem->cpmProblem)->weight;
                    }
                )->values()
            );

            $summary = $this->fillBillableProblemsNameAndCode($summary);
        }

        if ($this->lacksProblems($summary)) {
            $summary = $this->TO_DEPRECATE_fillProblems($patient, $summary, $this->getValidCcdProblems($patient));

            $summary = $this->fillBillableProblemsNameAndCode($summary);
        }

        if ( ! $skipValidation && $this->shouldGoThroughAttachProblemsAgain($summary, $patient)) {
            $patient->load(['billableProblems', 'ccdProblems']);
            $summary = $this->attachBillableProblems($patient, $summary);
        }

        return $this->determineStatusAndSave($summary);
    }

    /**
     * Attach the practice's default chargeable service to the given patient summary.
     *
     * @param $summary
     * @param array|ChargeableService|null $chargeableServiceId | The Chargeable Service Code to attach
     * @param bool                         $detach              | Whether to detach existing chargeable services, when using the sync function
     *
     * @return mixed
     */
    public function attachChargeableService($summary, $chargeableServiceId = null, $detach = false)
    {
        if ( ! $chargeableServiceId) {
            return $summary;
//        commented out on purpose. https://github.com/CircleLinkHealth/cpm-web/issues/1573
//            $chargeableServiceId = $summary->patient
//                ->primaryPractice
//                ->cpmSettings()
//                ->updateSummaryChargeableServices;
        }

        if (is_a($chargeableServiceId, ChargeableService::class)) {
            $chargeableServiceId = $chargeableServiceId->id;
        }

        if ( ! is_array($chargeableServiceId)) {
            $chargeableServiceId = [$chargeableServiceId];
        }

        $sync = $summary->chargeableServices()
            ->sync($chargeableServiceId, $detach);

        if ($sync['attached'] || $sync['detached'] || $sync['updated']) {
            $class = PatientMonthlySummary::class;
            Cache::tags(['practice.chargeable.services'])->forget("${class}:{$summary->id}:chargeableServices");
            $summary->load('chargeableServices');
        }

        return $summary;
    }

    public function attachChargeableServices(PatientMonthlySummary $summary)
    {
        $patient = $summary->patient;

        if ($this->shouldNotTouch($summary) && $summary->chargeableServices->isNotEmpty()) {
            return $summary;
        }

        $class = Practice::class;

        $chargeableServices = Cache::tags(['practice.chargeable.services'])->remember(
            "${class}:{$patient->primaryPractice->id}:chargeableServices",
            2,
            function () use ($patient) {
                return $patient->primaryPractice->chargeableServices->keyBy('code');
            }
        );

        $attach = $chargeableServices
            ->map(
                function ($service) use ($summary) {
                    if ($this->shouldAttachChargeableService($service, $summary)) {
                        return $service->id;
                    }
                }
            )
            ->filter()
            ->values()
            ->all();

        return $this->attachChargeableService($summary, $attach);
    }

    public function detachChargeableService($summary, $chargeableServiceId)
    {
        $detached = $summary->chargeableServices()
            ->detach($chargeableServiceId);

        $summary->load('chargeableServices');

        return $summary;
    }

    public function determineStatusAndSave(PatientMonthlySummary $summary)
    {
        $problemsDeleted = $this->removeDeletedConditions($summary);

        if ( ! $this->hasBillableProblemsNameAndCode($summary)) {
            $summary = $this->fillBillableProblemsNameAndCode($summary);
        }

        $summary = $this->approveIfShouldApprove($summary->patient, $summary);

        $summary = $this->setApprovalStatusAndNeedsQA($summary);

        if (($summary->approved && ! $summary->isDirty('approved')) || ($summary->rejected && ! $summary->isDirty(
            'rejected'
        )) || ($summary->needs_qa && ! $summary->isDirty('needs_qa'))) {
            return $summary;
        }

        $summary->save();

        if ($summary->approved && $summary->rejected) {
            $summary->approved = $summary->rejected = false;
        }

        if ($summary->approved && ($summary->problem_1 || $summary->problem_2)) {
            Problem::whereIn('id', array_filter([$summary->problem_1, $summary->problem_2]))
                ->update(
                    [
                        'billable' => true,
                    ]
                );

            Problem::whereNotIn(
                'id',
                array_filter([$summary->problem_1, $summary->problem_2])
            )
                ->update(
                    [
                        'billable' => false,
                    ]
                );
        }

        return $summary;
    }

    public function fillBillableProblemsNameAndCode(PatientMonthlySummary $summary)
    {
        $summary = $this->fillProblemNameAndCodeFromIdOrRelationship($summary, 1);

        return $this->fillProblemNameAndCodeFromIdOrRelationship($summary, 2);
    }

    public function fillProblemNameAndCodeFromIdOrRelationship(PatientMonthlySummary $summary, $problemNumber)
    {
        if ($summary->{"billable_problem${problemNumber}"} && $summary->{"billable_problem{$problemNumber}_code"}) {
            return $summary;
        }

        if ( ! is_int($problemNumber) || $problemNumber > 2 || $problemNumber < 1) {
            throw new InvalidArgumentException('Problem number must be an integer between 1 and 2.', 422);
        }

        if ( ! $summary->{"problem_${problemNumber}"}) {
            return $summary;
        }

        $problem = null;

        if ($summary->patient && $summary->patient->ccdProblems) {
            $problem = $summary->patient->ccdProblems
                ->firstWhere('id', $summary->{"problem_${problemNumber}"});
        }

        if ( ! $problem) {
            //this will never be reached @todo: confirm
            $problem = $summary->{"billableProblem${problemNumber}"};
        }

        $summary->{"billable_problem${problemNumber}"}      = optional($problem)->name;
        $summary->{"billable_problem{$problemNumber}_code"} = optional($problem)->icd10Code();

        return $summary;
    }

    /**
     * Get the patient's billable problems.
     *
     * @return Collection
     */
    public function getBillableProblems(User $patient)
    {
        return $patient->billableProblems
            ->map(
                function ($p) {
                    return [
                        'id'   => $p->id,
                        'name' => $p->name,
                        'code' => $p->icd10Code(),
                    ];
                }
            );
    }

    /**
     * @return mixed
     */
    public function getValidCcdProblems(User $patient)
    {
        return $patient->ccdProblems->where('cpm_problem_id', '!=', 1)
            ->where('is_monitored', '=', true)
            ->reject(
                function ($problem) {
                    return ! validProblemName($problem->name);
                }
            )
            ->reject(
                function ($problem) {
                    return ! $problem->icd10Code();
                }
            )
            ->unique('cpm_problem_id')
            ->sortByDesc(
                function ($ccdProblem) {
                    return optional($ccdProblem->cpmProblem)->weight;
                }
            )
            ->values();
    }

    public function hasBillableProblemsNameAndCode(PatientMonthlySummary $summary)
    {
        return $summary->billable_problem1
               && $summary->billable_problem1_code
               && $summary->billable_problem2
               && $summary->billable_problem2_code;
    }

    /**
     * Check whether the patient is lacking any billable problem codes.
     *
     * @return bool
     */
    public function lacksProblemCodes(PatientMonthlySummary $summary)
    {
        return ! $summary->billable_problem1_code || ! $summary->billable_problem2_code || $summary->billableBhiProblems()->whereNull('icd_10_code')->where(
            'icd_10_code',
            '=',
            ''
        )->exists();
    }

    /**
     * Check whether the patient is lacking any billable problems.
     *
     * @return bool
     */
    public function lacksProblems(PatientMonthlySummary $summary)
    {
        return ! ($summary->problem_1 && $summary->problem_2);
    }

    /**
     * This function will set field `needs_qa` on the $summary.
     * If the $summary needs to be QA'ed by a human, approved and rejected will be set to false.
     *
     * @return PatientMonthlySummary
     */
    public function setApprovalStatusAndNeedsQA(PatientMonthlySummary $summary)
    {
        $summary->needs_qa = ( ! $summary->approved && ! $summary->rejected)
                             || $this->lacksProblems($summary)
                             || $this->lacksProblemCodes($summary)
                             || 0 == $summary->no_of_successful_calls
                             || in_array($summary->patient->patientInfo->getCcmStatusForMonth($summary->month_year), [Patient::WITHDRAWN, Patient::PAUSED, Patient::WITHDRAWN_1ST_CALL])
                             || ! $summary->patient->billingProviderUser();

        if (
            ($summary->rejected || $summary->approved) && $summary->actor_id
        ) {
            $summary->needs_qa = false;
        }

        if ($summary->needs_qa) {
            $summary->approved = $summary->rejected = false;
        }

        return $summary;
    }

    /**
     * Determine whether a summary should be approved.
     *
     * @return bool
     */
    public function shouldApprove(User $patient, PatientMonthlySummary $summary)
    {
        return ! $this->lacksProblems($summary)
               && $summary->no_of_successful_calls >= 1
               && 'enrolled' == $patient->patientInfo->getCcmStatusForMonth($summary->month_year)
               && 1 != $summary->rejected
               && $summary->billable_problem1
               && $summary->billable_problem1_code
               && $summary->billable_problem2
               && $summary->billable_problem2_code;
    }

    public function shouldAttachProblems(User $patient, PatientMonthlySummary $summary)
    {
        return ! $this->approveIfShouldApprove($patient, $summary)->approved;
    }

    /**
     * Validate `problem_1` and `problem_2` on the given PatientMonthlySummary
     * If they are the same, then run the summary should go through attach records again.
     *
     * @return bool
     */
    public function shouldGoThroughAttachProblemsAgain(PatientMonthlySummary &$summary, User &$user)
    {
        //if the summary made it this far and still lacks problems
        //then it is safe to assume the patient does not have 2 machine detectable billable conditions
        //and we need human intervesion
        if ($this->lacksProblems($summary)) {
            return false;
        }

        $validate = (collect([$summary->problem_1, $summary->problem_2]))
            ->map(
                function ($problemId, $i) use ($user) {
                    $problem = $this->getValidCcdProblems($user)
                        ->where('id', '=', $problemId)
                        ->first();

                    if ( ! $problem) {
                        return false;
                    }

                    return $problem;
                }
            );

        if ($validate->get(0) && $validate->get(1)) {
            if (
                ($validate->get(0)->icd10Code() == $validate->get(1)->icd10Code())
                || $validate->get(0)->cpm_problem_id == $validate->get(1)->cpm_problem_id
            ) {
                if ($user->cpmProblems->where('id', '>', 1)->count() < 2) {
                    return false;
                }
                $validate[1] = false;
            }
        }

        foreach ($validate->all() as $index => $isValid) {
            $problemNo = $index + 1;

            if ( ! $isValid) {
                Problem::where('id', $summary->{"problem_${problemNo}"})
                    ->update(
                        [
                            'billable' => false,
                        ]
                    );
                $summary->{"problem_${problemNo}"} = null;
            }
        }

        return $this->lacksProblems($summary);
    }

    /**
     * Store a CCD Problem.
     *
     * @return bool|\Illuminate\Database\Eloquent\Model|void
     */
    public function storeCcdProblem(User $patient, array $arguments)
    {
        if (1 == $arguments['cpm_problem_id']) {
            return false;
        }

        return $this->patientRepo->storeCcdProblem($patient, $arguments);
    }

    /**
     * Save the most updated sum of calls and sum of successful calls to the given PatientMonthlySummary.
     *
     * @return PatientMonthlySummary
     */
    public function syncCallCounts(PatientMonthlySummary $summary)
    {
        $summary->no_of_calls            = $this->callRepo->numberOfCalls($summary->patient_id, $summary->month_year);
        $summary->no_of_successful_calls = $this->callRepo->numberOfSuccessfulCalls(
            $summary->patient_id,
            $summary->month_year
        );

        return $summary;
    }

    private function attachBhiProblem($summary)
    {
        $bhiProblems = $summary->attestedProblems
            ->where('cpmProblem.is_behavioral', '=', true)
            ->reject(
                function ($problem) {
                    return $problem && ! validProblemName($problem->name);
                }
            )
            ->unique('cpm_problem_id')
            ->sortByDesc(
                function ($ccdProblem) {
                    return optional($ccdProblem->cpmProblem)->weight;
                }
            )
            ->values()
            ->each(
                function ($problem) use ($summary) {
                    $summary->attachBillableProblem($problem->id, $problem->name, $problem->icd10Code(), 'bhi');

                    return false;
                }
            );

        return $summary;
    }

    private function fillProblemsUsingOlderSummary(PatientMonthlySummary &$summary)
    {
        $olderSummary = PatientMonthlySummary::wherePatientId($summary->patient_id)
            ->orderBy('month_year', 'desc')
            ->where(
                'month_year',
                '<=',
                $summary->month_year->copy()->subMonth()->startOfMonth()
            )
            ->whereApproved(true)
            ->with(['billableProblem1', 'billableProblem2'])
            ->first();

        if ($olderSummary) {
            if ($olderSummary->billableProblem1) {
                $summary->problem_1              = $olderSummary->problem_1;
                $summary->billable_problem1      = $olderSummary->billable_problem1;
                $summary->billable_problem1_code = $olderSummary->billable_problem1_code;
            } else {
                $summary->problem_1              = null;
                $summary->billable_problem1      = null;
                $summary->billable_problem1_code = null;
            }

            if ($olderSummary->billableProblem2) {
                $summary->problem_2              = $olderSummary->problem_2;
                $summary->billable_problem2      = $olderSummary->billable_problem2;
                $summary->billable_problem2_code = $olderSummary->billable_problem2_code;
            } else {
                $summary->problem_2              = null;
                $summary->billable_problem2      = null;
                $summary->billable_problem2_code = null;
            }

            if ($summary->problem_1 && $summary->problem_2) {
                $skipValidation = true;
            }
        }
    }

    private function removeDeletedConditions(PatientMonthlySummary &$summary)
    {
        $deleted = false;

        if ( ! $summary->billableProblem1()->exists()) {
            $summary->problem_1              = null;
            $summary->billable_problem1      = null;
            $summary->billable_problem1_code = null;
            $deleted                         = true;
        }

        if ( ! $summary->billableProblem2()->exists()) {
            $summary->problem_2              = null;
            $summary->billable_problem2      = null;
            $summary->billable_problem2_code = null;
            $deleted                         = true;
        }

        return $deleted;
    }

    /**
     * Decide whether or not to attach a chargeable service to a patient summary.
     *
     * @return bool
     */
    private function shouldAttachChargeableService(ChargeableService $service, PatientMonthlySummary $summary)
    {
        //FIXME: this is confusing. Might need a few extra parenthesis.
        return ChargeableService::BHI                        == $service->code && $summary->bhi_time >= self::MINUTES_20
               || ChargeableService::CCM                     == $service->code && $summary->ccm_time >= self::MINUTES_20
               || ChargeableService::GENERAL_CARE_MANAGEMENT == $service->code && $summary->ccm_time >= self::MINUTES_20
               || ChargeableService::CCM_PLUS_40             == $service->code && $summary->ccm_time >= self::MINUTES_40 && $summary->patient->primaryPractice->hasServiceCode(ChargeableService::CCM_PLUS_40)
               || ChargeableService::CCM_PLUS_60             == $service->code && $summary->ccm_time >= self::MINUTES_60 && $summary->patient->primaryPractice->hasServiceCode(ChargeableService::CCM_PLUS_60)
               || (ChargeableService::SOFTWARE_ONLY == $service->code && $summary->patient->primaryPractice->hasServiceCode(ChargeableService::SOFTWARE_ONLY)
                  && 0 == $summary->timeFromClhCareCoaches());
    }

    /**
     * Is it ok for the system to process this record?
     */
    private function shouldNotTouch(PatientMonthlySummary $summary): bool
    {
        return (bool) $summary->actor_id;
    }

    /**
     * Attempt to fill report from the patient's billable problems.
     *
     * @param Collection|Collection $billableProblems
     * @param int                   $tryCount
     * @param int                   $maxTries
     *
     * @return PatientMonthlySummary
     */
    private function TO_DEPRECATE_fillProblems(
        User $patient,
        PatientMonthlySummary $summary,
        $billableProblems,
        $tryCount = 0,
        $maxTries = 2
    ) {
        if ($billableProblems->isEmpty()) {
            return $summary;
        }

        $billableProblems = $billableProblems
            ->where('cpm_problem_id', '!=', 1)
            ->reject(
                function ($problem) {
                    return $problem && ! validProblemName($problem->name);
                }
            )
            ->unique('cpm_problem_id')
            ->values();

        for ($i = 1; $i <= 2; ++$i) {
            if ($billableProblems->isEmpty()) {
                continue;
            }

            $billableProblems = $billableProblems->values();

            $currentProblem = $summary->{"problem_${i}"};

            if ( ! $currentProblem) {
                $summary->{"problem_${i}"} = $billableProblems[0]['id'];
                $billableProblems->forget(0);
            } else {
                $forgetIndex = $billableProblems->search(
                    function ($item) use ($currentProblem) {
                        return $item['id'] == $currentProblem;
                    }
                );

                if (is_int($forgetIndex)) {
                    $billableProblems->forget($forgetIndex);
                }
            }
        }

        if ($summary->problem_1 == $summary->problem_2) {
            $summary->problem_2 = null;
            if ($patient->cpmProblems->where('id', '>', 1)->count() >= 2 && $tryCount < $maxTries) {
                $this->TO_DEPRECATE_fillProblems($patient, $summary, $billableProblems, ++$tryCount);
            }
        }

        return $summary;
    }
}
