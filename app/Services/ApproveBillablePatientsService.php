<?php namespace App\Services;

use App\Models\CCD\Problem;
use App\PatientMonthlySummary;
use App\Repositories\ApproveBillablePatientsRepository;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

class ApproveBillablePatientsService
{
    public $repo;

    public function __construct(ApproveBillablePatientsRepository $repo)
    {
        $this->repo = $repo;
    }

    public function counts($practiceId, Carbon $month)
    {
        $count['approved'] = 0;
        $count['toQA']     = 0;
        $count['rejected'] = 0;

        foreach ($this->repo->patientsWithSummaries($practiceId, $month)->get() as $patient) {
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
        return ! ($summary->billableProblem1 && $summary->billableProblem2);
    }

    public function patientsToApprove($practiceId, Carbon $month)
    {
        return $this->repo->billablePatients($practiceId, $month)
                          ->get()
                          ->map(function ($u) {
                              $info   = $u->patientInfo;
                              $summary = $u->patientSummaries->first();

                              $this->fillSummaryProblems($u, $summary);

                              $lacksProblems = $this->lacksProblems($summary);
                              //if patient was paused/withdrawn and acted upon already, it's not QA no more
                              $isNotEnrolledAndApproved = $summary->actor_id == null && in_array($info->ccm_status,
                                      ['withdrawn', 'paused']);

                              $summary->approved = $approved = !($lacksProblems || $summary->rejected == 1);

                              $rejected = $summary->rejected == 1;

                              $toQA = ! $approved && ! $rejected;

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
                                  'problem1'               => $summary->billableProblem1->name ?? null,
                                  'problem1_code'          => isset($summary->billableProblem1)
                                      ? $summary->billableProblem1->icd10Code()
                                      : null,
                                  'problem2'               => $summary->billableProblem2->name ?? null,
                                  'problem2_code'          => isset($summary->billableProblem2)
                                      ? $summary->billableProblem2->icd10Code()
                                      : null,
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
            $this->buildCcdProblemsFromCpmProblems($patient);
            $this->fillProblems($patient, $summary, $patient->ccdProblems);
        }
    }

    /**
     * Attempt to fill report from the patient's billable problems
     *
     * @param User $patient
     * @param PatientMonthlySummary $summary
     *
     * @return bool
     */
    public function fillProblems(User $patient, PatientMonthlySummary $summary, Collection $billableProblems)
    {
        if ($billableProblems->isEmpty()) {
            return false;
        }

        for ($i = 1; $i <= 2; $i++) {
            $billableProblems = $billableProblems->values();

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
        $ccdProblems = $patient->ccdProblems;

        $patient->cpmProblems->map(function ($problem) use ($ccdProblems, $patient) {
            if ($ccdProblems->where('cpm_problem_id', $problem->id)->count() == 0) {
                $newProblem = $patient->ccdProblems()->create([
                    'name'           => $problem->name,
                    'cpm_problem_id' => $problem->id,
                ]);

                $code = $newProblem->codes()->create([
                    'code_system_name' => 'ICD-10',
                    'code_system_oid'  => '2.16.840.1.113883.6.3',
                    'code'             => $problem->default_icd_10_code,
                ]);
            }
        });
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
