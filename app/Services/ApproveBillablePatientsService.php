<?php namespace App\Services;

use App\Patient;
use App\PatientMonthlySummary;
use App\Repositories\ApproveBillablePatientsRepository;
use App\User;
use Carbon\Carbon;
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
        $data = [];

        $patients = $this->repo->billablePatients($practiceId, $month);

        foreach ($patients as $u) {
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

            $toQA = ! $approved && ! $rejected;

            $report->save();

            $name = "<a href = " . URL::route('patient.careplan.show', [
                    'patient' => $u->id,
                    'page'    => 1,
                ]) . "  target='_blank' >" . $u->fullName . "</a>";

            $data[] = [
                'name'                   => $name,
                'provider'               => $u->billingProvider()->fullName,
                'practice'               => $u->primaryPractice->display_name,
                'dob'                    => $info->birth_date,
                'ccm'                    => round($report->ccm_time / 60, 2),
                'problem1'               => $report->billableProblem1->name,
                'problem1_code'          => $report->billableProblem1->icd10Code(),
                'problem2'               => $report->billableProblem2->name,
                'problem2_code'          => $report->billableProblem2->icd10Code(),
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
        }

        return $data;
    }

    private function fillSummaryProblems(User $patient, PatientMonthlySummary $summary)
    {
        if ($this->lacksProblems($summary)) {
            $this->attemptBillableProblems($patient, $summary);
        }
    }

    private function attemptBillableProblems(User $patient, PatientMonthlySummary $summary) {
        $billableProblems = $this->getBillableProblems($patient);

        if ($billableProblems->isEmpty()) {
            return false;
        }

        for ($i = 1; $i <= 2; $i++) {
            if ($billableProblems->isEmpty()) {
                continue;
            }

            $currentProblem = $summary->{"problem_$i"};

            if (!$currentProblem) {
                $summary->{"problem_$i"} = $billableProblems[0];
                $billableProblems->forget(0);
            } else {
                $forgetIndex = $billableProblems->search(function($item) use ($currentProblem) {
                    return $item['id'] == $currentProblem;
                });
                $billableProblems->forget($forgetIndex);
            }
        }

        if ($summary->problem_1 == $summary->problem_2) {
            $summary->problem_2 = null;
            $this->attemptBillableProblems($patient, $summary);
        }

        $summary->save();
    }


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

    private function lacksProblems(PatientMonthlySummary $summary)
    {
        return ! ($summary->billableProblem1 && $summary->billableProblem2);
    }

    private function chooseBillableProblems(User $patient, PatientMonthlySummary $summary)
    {
        $problemsWithIcd10Code = $patient->problemsWithIcd10Code();

        //First look for problemsWithIcd10Code in the report itself. If no problemsWithIcd10Code, then find problemsWithIcd10Code from CCM. If none, give select box
        for ($i = 0; $i < 2; $i++) {
            $problemName = 'billable_problem' . ($i + 1);
            $problemCode = 'billable_problem' . ($i + 1) . '_code';

            $billableProblems[$i]['name'] = $summary->$problemName;
            $billableProblems[$i]['code'] = $summary->$problemCode;

            if ( ! $summary->$problemName || ! $summary->$problemCode) {
                if (isset($problemsWithIcd10Code[$i])) {
                    $summary->$problemName        = $problemsWithIcd10Code[$i]->cpmProblem->name;
                    $summary->$problemCode        = $problemsWithIcd10Code[$i]->billing_code;
                    $billableProblems[$i]['name'] = $summary->$problemName;
                    $billableProblems[$i]['code'] = $summary->$problemCode;

                    if ( ! $summary->$problemCode) {
                        $lacksCode                    = true;
                        $billableProblems[$i]['code'] = "<button style='font-size: 10px' class='btn btn-primary codePicker' patient='$patient->fullName' name=$problemCode value='$options' id='$summary->id'>Select Code</button >";
                    }
                } else {
                    $name = 'billable_problem' . ($i + 1);

                    $lacksProblems = true;
                    $lacksCode     = true;

                    $billableProblems[$i]['name'] = "<button style='font-size: 10px' class='btn btn-primary problemPicker' patient='$patient->fullName' name=$name value='$options' id='$summary->id'>Select Problem</button >";
                    $billableProblems[$i]['code'] = "<button style='font-size: 10px' class='btn btn-primary codePicker' patient='$patient->fullName' name=$name value='$options' id='$summary->id'>Select Code</button >";
                }
            }
        }
    }
}
