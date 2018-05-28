<?php

namespace App\Repositories;


use App\ChargeableService;
use App\Exceptions\InvalidArgumentException;
use App\Models\CCD\Problem;
use App\PatientMonthlySummary;
use App\User;
use Illuminate\Support\Collection;

class PatientSummaryEloquentRepository
{
    public $patientRepo;

    public function __construct(PatientWriteRepository $patientRepo)
    {
        $this->patientRepo = $patientRepo;
    }

    /**
     * Determine whether a summary should be approved
     *
     * @param User $patient
     * @param PatientMonthlySummary $summary
     *
     * @return bool
     */
    public function shouldApprove(User $patient, PatientMonthlySummary $summary)
    {
        return ! $this->lacksProblems($summary)
               && $summary->no_of_successful_calls >= 1
               && $patient->patientInfo->ccm_status == 'enrolled'
               && $summary->rejected != 1
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
     * Attach 2 billable problems to the given PatientMonthlySummary, and returns a new summary.
     * NOTE: The summary is not persisted to the DB. You will have to call `->save()` on `$summary` this function will
     * return.
     *
     * @param User $patient
     * @param PatientMonthlySummary $summary
     *
     * @return PatientMonthlySummary
     */
    public function attachBillableProblems(User $patient, PatientMonthlySummary $summary)
    {
        if ($summary->actor_id) {
            return $summary;
        }

        $skipValidation = false;
        if ( ! $this->hasBillableProblemsNameAndCode($summary)) {
            $summary = $this->fillBillableProblemsNameAndCode($summary);
        }

        if ( ! $this->shouldAttachProblems($patient, $summary)) {
            return $this->determineStatusAndSave($summary);
        }

        if ($this->lacksProblems($summary)) {
            $olderSummary = PatientMonthlySummary::wherePatientId($summary->patient_id)
                ->orderBy('month_year', 'desc')
                ->where('month_year', '<=',
                    $summary->month_year->copy()->subMonth()->startOfMonth())
                ->whereApproved(true)
                ->first();

            if ($olderSummary) {
                $summary->problem_1              = $olderSummary->problem_1;
                $summary->problem_2              = $olderSummary->problem_2;
                $summary->billable_problem1      = $olderSummary->billable_problem1;
                $summary->billable_problem1_code = $olderSummary->billable_problem1_code;
                $summary->billable_problem2      = $olderSummary->billable_problem2;
                $summary->billable_problem2_code = $olderSummary->billable_problem2_code;
                $skipValidation                  = true;
            }
        }

        if ($this->lacksProblems($summary)) {
            $summary = $this->fillProblems($patient, $summary, $patient->ccdProblems->where('billable', '=', true));
        }

        if ($this->lacksProblems($summary)) {
            $summary = $this->fillProblems($patient, $summary, $this->getValidCcdProblems($patient));
        }

        if ( ! $skipValidation && $this->shouldGoThroughAttachProblemsAgain($summary, $patient)) {
            $patient->load(['billableProblems', 'ccdProblems']);
            $summary = $this->attachBillableProblems($patient, $summary);
        }

        return $this->determineStatusAndSave($summary);
    }

    /**
     * Attempt to fill report from the patient's billable problems
     *
     * @param User $patient
     * @param PatientMonthlySummary $summary
     * @param Collection|Collection $billableProblems
     * @param int $tryCount
     * @param int $maxTries
     *
     * @return PatientMonthlySummary
     */
    private function fillProblems(
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
            ->reject(function ($problem) {
                return $problem && ! validProblemName($problem->name);
            })
            ->unique('cpm_problem_id')
            ->values();

        for ($i = 1; $i <= 2; $i++) {
            if ($billableProblems->isEmpty()) {
                continue;
            }

            $billableProblems = $billableProblems->values();

            $currentProblem = $summary->{"problem_$i"};

            if ( ! $currentProblem) {
                $summary->{"problem_$i"} = $billableProblems[0]['id'];
                $billableProblems->forget(0);
            } else {
                $forgetIndex = $billableProblems->search(function ($item) use ($currentProblem) {
                    return $item['id'] == $currentProblem;
                });

                if (is_int($forgetIndex)) {
                    $billableProblems->forget($forgetIndex);
                }
            }
        }

        if ($summary->problem_1 == $summary->problem_2) {
            $summary->problem_2 = null;
            if ($patient->cpmProblems->where('id', '>', 1)->count() >= 2 && $tryCount < $maxTries) {
                $this->fillProblems($patient, $summary, $billableProblems, ++$tryCount);
            }
        }

        return $summary;
    }

    /**
     * Check whether the patient is lacking any billable problems
     *
     * @param PatientMonthlySummary $summary
     *
     * @return bool
     */
    public function lacksProblems(PatientMonthlySummary $summary)
    {
        return ! ($summary->problem_1 && $summary->problem_2);
    }

    /**
     * @param User $patient
     *
     * @return mixed
     */
    public function getValidCcdProblems(User $patient)
    {
        return $patient->ccdProblems->where('cpm_problem_id', '!=', 1)
            ->where('is_monitored', '=', true)
            ->reject(function ($problem) {
                return ! validProblemName($problem->name);
            })
            ->reject(function ($problem) {
                return ! $problem->icd10Code();
            })
            ->unique('cpm_problem_id')
            ->sortByDesc('cpm_problem_id')
            ->values();
    }

    /**
     * Store a CCD Problem
     *
     * @param User $patient
     * @param array $arguments
     *
     * @return bool|\Illuminate\Database\Eloquent\Model|void
     */
    public function storeCcdProblem(User $patient, array $arguments)
    {
        if ($arguments['cpm_problem_id'] == 1) {
            return false;
        }

        return $this->patientRepo->storeCcdProblem($patient, $arguments);
    }

    /**
     * Validate `problem_1` and `problem_2` on the given PatientMonthlySummary
     * If they are the same, then run the summary should go through attach records again
     *
     * @param PatientMonthlySummary $summary
     * @param User $user
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
            ->map(function ($problemId, $i) use ($user) {
                $problem = $this->getValidCcdProblems($user)
                    ->where('id', '=', $problemId)
                    ->first();

                if ( ! $problem) {
                    return false;
                }

                return $problem;
            });

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
                Problem::where('id', $summary->{"problem_$problemNo"})
                    ->update([
                        'billable' => false,
                    ]);
                $summary->{"problem_$problemNo"} = null;
            }
        }

        return $this->lacksProblems($summary);
    }

    /**
     * Get the patient's billable problems
     *
     * @param User $patient
     *
     * @return Collection
     */
    public function getBillableProblems(User $patient)
    {
        return $patient->billableProblems
            ->map(function ($p) {
                return [
                    'id'   => $p->id,
                    'name' => $p->name,
                    'code' => $p->icd10Code(),
                ];
            });
    }

    public function approveIfShouldApprove(User $patient, PatientMonthlySummary $summary)
    {
        if ( ! $this->lacksProblems($summary)) {
            if ( ! $summary->approved && ! $summary->rejected && $this->shouldApprove($patient, $summary)) {
                $summary->approved = true;

                if ($summary->problem_1 && $summary->problem_2) {
                    Problem::whereNotIn('id',
                        array_filter([$summary->problem_1, $summary->problem_2]))
                           ->update([
                               'billable' => false,
                           ]);
                }

                Problem::whereIn('id', array_filter([$summary->problem_1, $summary->problem_2]))
                    ->update([
                        'billable' => true,
                    ]);
            }
        }

        return $summary;
    }

    public function determineStatusAndSave(PatientMonthlySummary $summary)
    {
        if ( ! $this->hasBillableProblemsNameAndCode($summary)) {
            $summary = $this->fillBillableProblemsNameAndCode($summary);
        }

        $summary = $this->approveIfShouldApprove($summary->patient, $summary);

        $summary->needs_qa = ( ! $summary->approved && ! $summary->rejected)
                             || $this->lacksProblems($summary)
                             || $summary->no_of_successful_calls == 0
                             || in_array($summary->patient->patientInfo->ccm_status, ['withdrawn', 'paused']);

        if ($summary->rejected || $summary->approved || $summary->actor_id) {
            $summary->needs_qa = false;
        }

        if ($summary->needs_qa) {
            $summary->approved = $summary->rejected = false;
        }

        if (($summary->approved && ! $summary->isDirty('approved')) || ($summary->rejected && ! $summary->isDirty('rejected')) || ($summary->needs_qa && ! $summary->isDirty('needs_qa'))) {
            return $summary;
        }

        $summary->save();

        if ($summary->approved && $summary->rejected) {
            $summary->approved = $summary->rejected = false;
        }

        if ($summary->approved && ($summary->problem_1 || $summary->problem_2)) {
            Problem::whereIn('id', array_filter([$summary->problem_1, $summary->problem_2]))
                ->update([
                    'billable' => true,
                ]);

            Problem::whereNotIn('id',
                array_filter([$summary->problem_1, $summary->problem_2]))
                   ->update([
                       'billable' => false,
                   ]);
        }

        return $summary;
    }

    /**
     * Attach the practice's default chargeable service to the given patient summary.
     *
     * @param $summary
     * @param null $chargeableServiceId | The Chargeable Service Code to attach
     * @param bool $detach | Whether to detach existing chargeable services, when using the sync function
     *
     * @return mixed
     */
    public function attachDefaultChargeableService($summary, $chargeableServiceId = null, $detach = false)
    {
        if ( ! $chargeableServiceId) {
            return $summary;

//        commented out on purpose. https://github.com/CircleLinkHealth/cpm-web/issues/1573
//            $chargeableServiceId = $summary->patient
//                ->primaryPractice
//                ->cpmSettings()
//                ->updateSummaryChargeableServices;
        }

        $sync = $summary->chargeableServices()
            ->sync($chargeableServiceId, $detach);

        if ($sync['attached'] || $sync['detached'] || $sync['updated']) {
            $summary->load('chargeableServices');
        }

        return $summary;
    }

    public function detachDefaultChargeableService($summary, $defaultCodeId)
    {
        $detached = $summary->chargeableServices()
            ->detach($defaultCodeId);

        $summary->load('chargeableServices');

        return $summary;
    }

    public function hasBillableProblemsNameAndCode(PatientMonthlySummary $summary)
    {
        return $summary->billable_problem1
               && $summary->billable_problem1_code
               && $summary->billable_problem2
               && $summary->billable_problem2_code;
    }

    public function fillBillableProblemsNameAndCode(PatientMonthlySummary $summary)
    {
        $summary = $this->fillProblemNameAndCodeFromIdOrRelationship($summary, 1);
        $summary = $this->fillProblemNameAndCodeFromIdOrRelationship($summary, 2);

        return $summary;
    }

    public function fillProblemNameAndCodeFromIdOrRelationship(PatientMonthlySummary $summary, $problemNumber)
    {
        if ($summary->{"billable_problem$problemNumber"} && $summary->{"billable_problem{$problemNumber}_code"}) {
            return $summary;
        }

        if ( ! is_int($problemNumber) || $problemNumber > 2 || $problemNumber < 1) {
            throw new InvalidArgumentException('Problem number must be an integer between 1 and 2.', 422);
        }

        if ( ! $summary->{"problem_$problemNumber"}) {
            return $summary;
        }

        $problem = null;

        if ($summary->patient && $summary->patient->ccdProblems) {
            $problem = $summary->patient->ccdProblems
                ->firstWhere('id', $summary->{"problem_$problemNumber"});
        }

        if ( ! $problem) {
            //this will never be reached @todo: confirm
            $problem = $summary->{"billableProblem$problemNumber"};
        }

        $summary->{"billable_problem$problemNumber"}        = optional($problem)->name;
        $summary->{"billable_problem{$problemNumber}_code"} = optional($problem)->icd10Code();

        return $summary;
    }

    public function attachChargeableServices(User $patient, PatientMonthlySummary $summary)
    {
        if ($summary->actor_id) {
            return $summary;
        }

        $chargeableServices       = null;
        $attachChargeableServices = false;

        if ($patient->primaryPractice->hasServiceCode('CPT 99484')) {
            $chargeableServices       = ChargeableService::get()->keyBy('code');
            $attachChargeableServices = true;
        }

        if ($attachChargeableServices) {
            $totalTime = $summary->bhi_time + $summary->ccm_time;

            if ($summary->ccm_time > 1199 && $summary->bhi_time > 1199) {
                $summary = $this->attachDefaultChargeableService($summary, $chargeableServices['CPT 99484'], true);
                $summary = $this->attachDefaultChargeableService($summary, $chargeableServices['CPT 99490']);
            } elseif ($totalTime > 1199 && $summary->bhi_time < 1200) {
                $summary = $this->attachDefaultChargeableService($summary, $chargeableServices['CPT 99490'], true);
            } elseif ($totalTime < 2399 && $summary->bhi_time > 1199) {
                $summary = $this->attachDefaultChargeableService($summary, $chargeableServices['CPT 99484'], true);
            }
        }

        return $summary;
    }
}