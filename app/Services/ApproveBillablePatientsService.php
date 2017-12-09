<?php namespace App\Services;

use App\Models\CCD\Problem;
use App\PatientMonthlySummary;
use App\Repositories\ApproveBillablePatientsRepository;
use App\Repositories\PatientRepository;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

class ApproveBillablePatientsService
{
    public $approvePatientsRepo;
    public $patientRepo;

    public function __construct(ApproveBillablePatientsRepository $approvePatientsRepo, PatientRepository $patientRepo)
    {
        $this->approvePatientsRepo = $approvePatientsRepo;
        $this->patientRepo         = $patientRepo;
    }

    public function counts($practiceId, Carbon $month)
    {
        $count['approved'] = 0;
        $count['toQA']     = 0;
        $count['rejected'] = 0;

        foreach ($this->approvePatientsRepo->patientsWithSummaries($practiceId, $month)->get() as $patient) {
            $report = $patient->patientSummaries->first();

            if (($report->rejected == 0 && $report->approved == 0) || $this->lacksProblems($report)) {
                $count['toQA'] += 1;
            } else if ($report->rejected == 1) {
                $count['rejected'] += 1;
            } else if ($report->approved == 1) {
                $count['approved'] += 1;
            }
        }

        return $count;
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

    public function patientsToApprove($practiceId, Carbon $month)
    {
        return $this->approvePatientsRepo->billablePatients($practiceId, $month)
                                         ->get()
                                         ->map(function ($u) {
                                             $info    = $u->patientInfo;
                                             $summary = $u->patientSummaries->first();

                                             $this->fillSummaryProblems($u, $summary);

                                             $lacksProblems = $this->lacksProblems($summary);

                                             $summary->approved = $approved = ! ($lacksProblems || $summary->rejected == 1);

                                             $rejected = $summary->rejected == 1;

                                             $problem1     = isset($summary->problem_1) && $u->ccdProblems
                                                 ? $u->ccdProblems->where('id', $summary->problem_1)->first()
                                                 : null;
                                             $problem1Code = isset($problem1)
                                                 ? $problem1->icd10Code()
                                                 : null;
                                             $problem1Name = $problem1->name ?? null;

                                             $problem2     = isset($summary->problem_2) && $u->ccdProblems
                                                 ? $u->ccdProblems->where('id', $summary->problem_2)->first()
                                                 : null;
                                             $problem2Code = isset($problem2)
                                                 ? $problem2->icd10Code()
                                                 : null;
                                             $problem2Name = $problem2->name ?? null;

                                             $toQA = ( ! $approved && ! $rejected) || ! $problem1Code || ! $problem2Code || ! $problem1Name || ! $problem2Name || $summary->no_of_successful_calls == 0 || in_array($info->ccm_status,
                                                     ['withdrawn', 'paused']);

                                             if (($rejected || $approved) && $summary->actor_id) {
                                                 $toQA = false;
                                             }

                                             if ($toQA) {
                                                 $approved = $rejected = false;
                                             }

                                             $summary->save();

                                             $name = "<a href = " . URL::route('patient.careplan.show', [
                                                     'patient' => $u->id,
                                                     'page'    => 1,
                                                 ]) . "  target='_blank' >" . $u->fullName . "</a>";

                                             return [
                                                 'name'                   => $name,
                                                 'provider'               => $u->billingProvider()->fullName,
                                                 'practice'               => $u->primaryPractice->display_name,
                                                 'dob'                    => $info->birth_date,
                                                 'ccm'                    => round($summary->ccm_time / 60, 2),
                                                 'problem1'               => $problem1Name,
                                                 'problem1_code'          => $problem1Code,
                                                 'problem2'               => $problem2Name,
                                                 'problem2_code'          => $problem2Code,
                                                 'problems'               => $this->ccdProblems($u),
                                                 'no_of_successful_calls' => $summary->no_of_successful_calls,
                                                 'status'                 => $info->ccm_status,
                                                 'approve'                => $approved,
                                                 'reject'                 => $rejected,
                                                 'report_id'              => $summary->id,
                                                 'qa'                     => $toQA,
                                                 'lacksProblems'          => $lacksProblems,

                                             ];
                                         });
    }

    public function fillSummaryProblems(User $patient, PatientMonthlySummary $summary)
    {
        if ($this->lacksProblems($summary)) {
            $this->fillProblems($patient, $summary, $this->getBillableProblems($patient));
        }

        if ($this->lacksProblems($summary)) {
            $this->fillProblems($patient, $summary, $patient->ccdProblems);
        }

        if ($this->lacksProblems($summary)) {
            $newProblems = $this->buildCcdProblemsFromCpmProblems($patient);

            if ($newProblems->isNotEmpty()) {
                $patient->load('ccdProblems');
            }

            $this->fillProblems($patient, $summary, $newProblems);
        }
    }

    /**
     * Attempt to fill report from the patient's billable problems
     *
     * @param User $patient
     * @param PatientMonthlySummary $summary
     *
     * @param Collection|Collection $billableProblems
     *
     * @return bool
     */
    private function fillProblems(User $patient, PatientMonthlySummary $summary, $billableProblems)
    {
        if ($billableProblems->isEmpty()) {
            return false;
        }

        for ($i = 1; $i <= 2; $i++) {
            $billableProblems = $billableProblems
                ->where('cpm_problem_id', '!=', null)
                ->values();

            if ($billableProblems->isEmpty()) {
                continue;
            }

            $currentProblem = $summary->{"problem_$i"};

            if ( ! $currentProblem) {
                $summary->{"problem_$i"} = $billableProblems[0]['id'];
                Problem::where('id', $summary->{"problem_$i"})
                       ->update([
                           'billable' => true,
                       ]);
                $billableProblems->forget(0);
            } else {
                $forgetIndex = $billableProblems->search(function ($item) use ($currentProblem) {
                    return $item['id'] == $currentProblem;
                });

                if ($forgetIndex) {
                    $billableProblems->forget($forgetIndex);
                }
            }
        }

        if ($summary->problem_1 == $summary->problem_2) {
            $summary->problem_2 = null;
            $this->fillProblems($patient, $summary, $billableProblems);
        }

        $summary->save();
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

    public function buildCcdProblemsFromCpmProblems(User $patient)
    {
        $newProblems = [];
        $ccdProblems = $patient->ccdProblems;

        $patient->cpmProblems->map(function ($problem) use ($ccdProblems, $patient, &$newProblems) {
            if ($ccdProblems->where('cpm_problem_id', $problem->id)->count() == 0) {
                $newProblems[] = $this->storeCcdProblem($patient, [
                    'name'             => $problem->name,
                    'cpm_problem_id'   => $problem->id,
                    'code_system_name' => 'ICD-10',
                    'code_system_oid'  => '2.16.840.1.113883.6.3',
                    'code'             => $problem->default_icd_10_code,
                    'billable'         => true,
                ]);
            }
        });

        return collect($newProblems);
    }

    public function storeCcdProblem(User $patient, array $arguments)
    {
        return $this->patientRepo->storeCcdProblem($patient, $arguments);
    }

    public function ccdProblems(User $patient)
    {
        return $patient->ccdProblems->map(function ($prob) {
            return [
                'id'   => $prob->id,
                'name' => $prob->name,
                'code' => $prob->icd10Code(),
            ];
        });
    }
}
