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

    public function counts($practiceId, Carbon $month) {
        $count['approved'] = 0;
        $count['toQA'] = 0;
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

    public function patientsToApprove($practiceId, Carbon $month)
    {
        return $this->repo->billablePatients($practiceId, $month)
                          ->get()
                          ->map(function ($u) {
                              $info   = $u->patientInfo;
                              $report = $u->patientSummaries->first();

                              $this->fillSummaryProblems($u, $report);

                              $lacksProblems = $this->lacksProblems($report);
                              //if patient was paused/withdrawn and acted upon already, it's not QA no more
                              $isNotEnrolledAndApproved = $report->actor_id == null && in_array($info->ccm_status,
                                      ['withdrawn', 'paused']);

                              $approved = $lacksProblems || $report->rejected == 1
                                  ? ''
                                  : 'checked';

                              $rejected = $report->rejected == 1
                                  ? 'checked'
                                  : '';

                              $report->approved = ! empty($approved);

                              $toQA = ! $approved && ! $rejected
                                  ? 1
                                  : '';

                              $report->save();

                              $name = "<a href = " . URL::route('patient.careplan.show', [
                                      'patient' => $u->id,
                                      'page'    => 1,
                                  ]) . "  target='_blank' >" . $u->fullName . "</a>";

                              return [
                                  'name'                   => $name,
                                  'provider'               => $u->billingProvider()->fullName,
                                  'practice'               => $u->primaryPractice->display_name,
                                  'dob'                    => $info->birth_date,
                                  'ccm'                    => round($report->ccm_time / 60, 2),
                                  'problem1'               => $report->billableProblem1->name ?? null,
                                  'problem1_code'          => isset($report->billableProblem1)
                                      ? $report->billableProblem1->icd10Code()
                                      : null,
                                  'edit1'                  => $this->ccdProblems($u),
                                  'problem2'               => $report->billableProblem2->name ?? null,
                                  'problem2_code'          => isset($report->billableProblem2)
                                      ? $report->billableProblem2->icd10Code()
                                      : null,
                                  'edit2'                  => $this->ccdProblems($u),
                                  'no_of_successful_calls' => $report->no_of_successful_calls,
                                  'status'                 => $info->ccm_status,
                                  'approve'                => $approved,
                                  'reject'                 => $rejected,
                                  //used to reference cells for jQuery ops
                                  'report_id'              => $report->id ?? null,
                                  //this is a hidden sorter
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
                'code' => $prob->icd10Code()
            ];
        });
    }
}
