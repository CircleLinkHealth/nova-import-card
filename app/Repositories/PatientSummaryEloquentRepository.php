<?php

namespace App\Repositories;


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
    public function shouldApprove(User $patient, PatientMonthlySummary $summary) {
        return !$this->lacksProblems($summary)
               && $summary->no_of_successful_calls >= 1
               && $patient->patientInfo->ccm_status == 'enrolled'
               && $summary->rejected != 1;
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
        $approved = $this->approveIfShouldApprove($patient, $summary);

        if ($approved) {
            return $summary;
        }

        if ($this->lacksProblems($summary)) {
            $this->fillProblems($patient, $summary, $patient->ccdProblems->where('billable', '=', true));
        }

        if ($this->lacksProblems($summary)) {
            $this->fillProblems($patient, $summary, $this->getValidCcdProblems($patient));
        }

        if ($this->lacksProblems($summary)) {
            $newProblems = $this->buildCcdProblemsFromCpmProblems($patient);

            if ($newProblems->isNotEmpty()) {
                $patient->load('ccdProblems');
            }

            $this->fillProblems($patient, $summary, $newProblems);
        }

        if ( ! $this->validateSummaryProblems($summary, $patient)) {
            $patient->load(['billableProblems', 'ccdProblems']);
            $this->attachBillableProblems($patient, $summary);
        }

        $lacksProblems = $this->lacksProblems($summary);

        $summary->approved = $this->shouldApprove($patient, $summary);

        $summary->save();

        if ($summary->problem_1 && $summary->problem_2) {
            Problem::whereNotIn('id',
                array_filter([$summary->problem_1, $summary->problem_2]))
                   ->update([
                       'billable' => false,
                   ]);
        }

        return $summary;
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
     * @return bool
     */
    private function fillProblems(User $patient, PatientMonthlySummary $summary, $billableProblems, $tryCount = 0, $maxTries = 2)
    {
        if ($billableProblems->isEmpty()) {
            return;
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

        Problem::whereIn('id', array_filter([$summary->problem_1, $summary->problem_2]))
               ->update([
                   'billable' => true,
               ]);
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
                                    ->reject(function ($problem) {
                                        return ! validProblemName($problem->name);
                                    })
                                    ->reject(function ($problem) {
                                        return ! $problem->icd10Code();
                                    })
                                    ->unique('cpm_problem_id')
                                    ->values();
    }

    /**
     * Create CCDProblems from related CPMProblems
     *
     * @param User $patient
     *
     * @return Collection
     */
    public function buildCcdProblemsFromCpmProblems(User $patient)
    {
        $newProblems = [];
        $ccdProblems = $patient->ccdProblems;

        $updated  = null;
        $toUpdate = $this->getValidCcdProblems($patient)
                         ->where('billable', '=', null)
                         ->take(2);

        if ($toUpdate->isNotEmpty()) {
            $updated = Problem::whereIn('id', $toUpdate->pluck('id')->all())->update([
                'billable' => true,
            ]);
        }

        if ($updated) {
            return $toUpdate;
        }

        $newProblems = $patient->cpmProblems->reject(function ($problem) use ($ccdProblems, $patient) {
            if ($ccdProblems->where('cpm_problem_id', $problem->id)->count() == 0) {
                return false;
            }

            return true;
        })
                                            ->filter()
                                            ->values()
                                            ->take(2)
                                            ->map(function ($problem) use ($ccdProblems, $patient) {
                                                return $this->storeCcdProblem($patient, [
                                                    'name'             => $problem->name,
                                                    'cpm_problem_id'   => $problem->id,
                                                    'code_system_name' => 'ICD-10',
                                                    'code_system_oid'  => '2.16.840.1.113883.6.3',
                                                    'code'             => $problem->default_icd_10_code,
                                                    'billable'         => true,
                                                ]);
                                            });

        return collect($newProblems);
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
     *
     * @param PatientMonthlySummary $summary
     * @param User $user
     *
     * @return bool
     */
    public function validateSummaryProblems(PatientMonthlySummary &$summary, User &$user)
    {
        if ($this->lacksProblems($summary)) {
            return true;
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
                    return true;
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

        return ! $this->lacksProblems($summary);
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
        if (!$this->lacksProblems($summary)) {
            if (!$summary->approved && !$summary->rejected && $this->shouldApprove($patient, $summary)) {
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

                $summary->save();
            }

            return $summary;
        }

        return false;
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
}