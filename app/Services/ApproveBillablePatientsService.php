<?php namespace App\Services;

use App\Models\CCD\Problem;
use App\Repositories\BillablePatientsEloquentRepository;
use App\Repositories\PatientSummaryEloquentRepository;
use App\User;
use Carbon\Carbon;

class ApproveBillablePatientsService
{
    public $approvePatientsRepo;
    public $patientSummaryRepo;

    public function __construct(
        BillablePatientsEloquentRepository $approvePatientsRepo,
        PatientSummaryEloquentRepository $patientSummaryRepo
    ) {
        $this->approvePatientsRepo = $approvePatientsRepo;
        $this->patientSummaryRepo  = $patientSummaryRepo;
    }

    public function counts($practiceId, Carbon $month)
    {
        $count['approved'] = 0;
        $count['toQA']     = 0;
        $count['rejected'] = 0;

        foreach ($this->approvePatientsRepo->patientsWithSummaries($practiceId, $month)->get() as $patient) {
            $report = $patient->patientSummaries->first();

            if (($report->rejected == 0 && $report->approved == 0) || $this->patientSummaryRepo->lacksProblems($report)) {
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
        return $this->approvePatientsRepo->billablePatients($practiceId, $month)
                                         ->get()
                                         ->map(function ($u) {
                                             $summary = $u->patientSummaries->first();

                                             if ($this->patientSummaryRepo->lacksProblems($summary)) {
                                                 $this->patientSummaryRepo->attachBillableProblems($u, $summary);
                                             }

                                             $lacksProblems = $this->patientSummaryRepo->lacksProblems($summary);

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

                                             $toQA = ( ! $approved && ! $rejected)
                                                     || ! $problem1Code || ! $problem2Code || ! $problem1Name || ! $problem2Name
                                                     || $summary->no_of_successful_calls == 0
                                                     || in_array($u->patientInfo->ccm_status, ['withdrawn', 'paused']);

                                             if (($rejected || $approved) && $summary->actor_id) {
                                                 $toQA = false;
                                             }

                                             if ($toQA) {
                                                 $approved = $rejected = false;
                                             }

                                             $summary->save();

                                             if ($summary->problem_1 && $summary->problem_2) {
                                                 Problem::whereNotIn('id',
                                                     array_filter([$summary->problem_1, $summary->problem_2]))
                                                        ->update([
                                                            'billable' => false,
                                                        ]);
                                             }

                                             $bP = $u->careTeamMembers->where('type', '=', 'billing_provider')->first();

                                             $name = "<a href = " . route('patient.careplan.show', [
                                                     'patient' => $u->id,
                                                     'page'    => 1,
                                                 ]) . "  target='_blank' >" . $u->fullName . "</a>";

                                             $result = [
                                                 'mrn'                    => $u->patientInfo->mrn_number,
                                                 'name'                   => $name,
                                                 'provider'               => $bP
                                                     ? $bP->user->fullName
                                                     : '',
                                                 'practice'               => $u->primaryPractice->display_name,
                                                 'dob'                    => $u->patientInfo->birth_date,
                                                 'ccm'                    => round($summary->ccm_time / 60, 2),
                                                 'problem1'               => $problem1Name,
                                                 'problem1_code'          => $problem1Code,
                                                 'problem2'               => $problem2Name,
                                                 'problem2_code'          => $problem2Code,
                                                 'problems'               => $this->allCcdProblems($u),
                                                 'no_of_successful_calls' => $summary->no_of_successful_calls,
                                                 'status'                 => $u->patientInfo->ccm_status,
                                                 'approve'                => $approved,
                                                 'reject'                 => $rejected,
                                                 'report_id'              => $summary->id,
                                                 'qa'                     => $toQA,
                                                 'lacksProblems'          => $lacksProblems,

                                             ];

                                             return $result;
                                         });
    }

    public function allCcdProblems(User $patient)
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
