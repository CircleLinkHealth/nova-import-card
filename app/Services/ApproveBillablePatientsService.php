<?php namespace App\Services;

use App\Repositories\BillablePatientsEloquentRepository;
use App\Repositories\PatientSummaryEloquentRepository;
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
        $summaries = $this->approvePatientsRepo->billablePatients($practiceId, $month)
                                         ->paginate();
        $summaries->getCollection()->transform(function ($u) {
            return $this->patientSummaryRepo->attachBillableProblems($u,
                $u->patientSummaries->first());
        });

        return $summaries;
    }

    public function transformPatientsToApprove($practiceId, Carbon $month) {
        $summaries = $this->patientsToApprove($practiceId, $month);
        
        $summaries->getCollection()->transform(function ($summary) {
            $problems = $summary->patient->ccdProblems()->get()->map(function ($prob) {
                return [
                    'id'   => $prob->id,
                    'name' => $prob->name,
                    'code' => $prob->icd10Code(),
                ];
            });
            
            $problem1 = (isset($summary->problem_1) && $problems)
            ? $problems->where('id', $summary->problem_1)->first()
            : null;
            $problem1Code = $problem1 ? $problem1['code'] : null;
            $problem1Name = $problem1 ? ($problem1['name']) : null;
            
            $problem2 = (isset($summary->problem_2) && $problems)
                ? $problems->where('id', $summary->problem_2)->first()
                : null;
            $problem2Code = $problem2 ? $problem2['code'] : null;
            $problem2Name = $problem2 ? ($problem2['name']) : null;
    
            $lacksProblems = ! $problem1Code || ! $problem2Code || ! $problem1Name || ! $problem2Name;
    
            $toQA = ( ! $summary->approved && ! $summary->rejected)
                    || $lacksProblems
                    || $summary->no_of_successful_calls == 0
                    || in_array($summary->patient->patientInfo->ccm_status, ['withdrawn', 'paused']);
    
            if (($summary->rejected || $summary->approved) && $summary->actor_id) {
                $toQA = false;
            }
    
            if ($toQA) {
                $summary->approved = $summary->rejected = false;
            }
    
            $bP = $summary->patient->careTeamMembers->where('type', '=', 'billing_provider')->first();
    
            $name = $summary->fullName;
      
            return [
                'id'                     => $summary->patient->id,
                'mrn'                    => $summary->patient->patientInfo->mrn_number,
                'name'                   => $name,
                'url'                    => route('patient.careplan.show', [
                                                'patient' => $summary->id,
                                                'page'    => 1
                                            ]),
                'provider'               => ($bP && $bP->user)
                    ? $bP->user->fullName
                    : '',
                'practice'               => $summary->patient->primaryPractice->display_name,
                'dob'                    => $summary->patient->patientInfo->birth_date,
                'ccm'                    => round($summary->ccm_time / 60, 2),
                'problem1'               => $problem1Name,
                'problem1_code'          => $problem1Code,
                'problem2'               => $problem2Name,
                'problem2_code'          => $problem2Code,
                'problems'               => $problems,
                'no_of_successful_calls' => $summary->no_of_successful_calls,
                'status'                 => $summary->patient->patientInfo->ccm_status,
                'approve'                => $summary->approved,
                'reject'                 => $summary->rejected,
                'report_id'              => $summary->id,
                'qa'                     => $toQA,
                'lacksProblems'          => $lacksProblems
            ];    
        });

        return $summaries;
    }
}
