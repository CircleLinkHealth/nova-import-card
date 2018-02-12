<?php namespace App\Services;

use App\Http\Resources\ApprovableBillablePatient;
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
        $summaries = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month)
                                         ->paginate();
        $summaries->getCollection()->transform(function ($summary) {
            return $this->patientSummaryRepo->attachBillableProblems($summary->patient, $summary);
        });

        return $summaries;
    }

    public function transformPatientsToApprove($practiceId, Carbon $month) {
        $summaries = $this->patientsToApprove($practiceId, $month);
        
        $summaries->getCollection()->transform(function ($summary) {
            return ApprovableBillablePatient::make($summary);
        });

        return $summaries;
    }

    public function billablePatientSummaries($practiceId, Carbon $month) {
        return $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month);
    }
}
