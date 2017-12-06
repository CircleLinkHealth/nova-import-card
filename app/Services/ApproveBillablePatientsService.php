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
    private $repo;

    public function __construct(ApproveBillablePatientsRepository $repo)
    {
        $this->repo = $repo;
    }

    public function patientsToApprove($practiceId, Carbon $month)
    {
        return $this->repo->billablePatients($practiceId, $month)
                          ->get()
                          ->map(function ($u) {
                              $info   = $u->patientInfo;
                              $report = $info->patientSummaries->first();

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

                              $report->load('billableProblem1');
                              $report->load('billableProblem2');

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
                                  'edit1'                  => $this->selectProblemButton(1, $u, $report),
                                  'problem2'               => $report->billableProblem2->name ?? null,
                                  'problem2_code'          => isset($report->billableProblem2)
                                      ? $report->billableProblem2->icd10Code()
                                      : null,
                                  'edit2'                  => $this->selectProblemButton(2, $u, $report),
                                  'no_of_successful_calls' => $report->no_of_successful_calls,
                                  'status'                 => $info->ccm_status,
                                  'approve'                => "<input type = \"checkbox\" class='approved_checkbox' id='$report->id' $approved>",
                                  'reject'                 => "<input type=\"checkbox\" class='rejected_checkbox' id='$report->id' $rejected>",
                                  //used to reference cells for jQuery ops
                                  'report_id'              => $report->id ?? null,
                                  //this is a hidden sorter
                                  'qa'                     => $toQA,
                                  'problemsWithIcd10Code'  => '',
                                  'lacksProblems'          => $lacksProblems,

                              ];
                          });
    }

    private function fillSummaryProblems(User $patient, PatientMonthlySummary $summary)
    {
        if ($this->lacksProblems($summary)) {
            $this->fillProblems($patient, $summary, $this->getBillableProblems($patient));
        }

        if ($this->lacksProblems($summary)) {
            $this->fillProblems($patient, $summary, $patient->problemsWithIcd10Code());
        }

        if ($this->lacksProblems($summary)) {
            $this->buildCcdProblemsFromCpmProblems($patient);
            $this->fillProblems($patient, $summary, $patient->problemsWithIcd10Code());
        }
    }

    /**
     * Check whether the patient is lacking any billable problems
     *
     * @param PatientMonthlySummary $summary
     *
     * @return bool
     */
    private function lacksProblems(PatientMonthlySummary $summary)
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
    private function fillProblems(User $patient, PatientMonthlySummary $summary, Collection $billableProblems)
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
    private function getBillableProblems(User $patient)
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

    private function buildCcdProblemsFromCpmProblems(User $patient)
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

    private function selectProblemButton($number, User $patient, PatientMonthlySummary $summary)
    {
        $name    = "billable_problem$number";
        $options = $patient->ccdProblems()->get()->implode('name', '|');

        return "<button style='font-size: 10px' class='btn btn-primary problemPicker' patient='$patient->fullName' name=$name value='$options' id='$summary->id'>Edit</button >";
    }
}
