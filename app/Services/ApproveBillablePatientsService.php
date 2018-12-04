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
        $count['approved'] = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month)
            ->where('approved', '=', true)
            ->where('rejected', '=', false)
            ->count();

        $count['toQA'] = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month)
            ->where('approved', '=', false)
            ->where('rejected', '=', false)
            ->count();

        $count['rejected'] = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month)
            ->where('rejected', '=', true)
            ->where('approved', '=', false)
            ->count();

        return $count;
    }

    public function patientsToApprove($practiceId, Carbon $month)
    {
        $summaries = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month)
            ->paginate();
        $summaries->getCollection()->transform(function ($summary) {
            $summary = $this->patientSummaryRepo
                ->attachChargeableService($summary);

            return $this->patientSummaryRepo->attachBillableProblems($summary->patient, $summary);
        });

        return $summaries;
    }

    public function transformPatientsToApprove($practiceId, Carbon $month)
    {
        $summaries = $this->patientsToApprove($practiceId, $month);

        $summaries->getCollection()->transform(function ($summary) {
            return ApprovableBillablePatient::make($summary);
        });

        return $summaries;
    }

    public function billablePatientSummaries($practiceId, Carbon $month)
    {
        return $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month);
    }

    public function attachDefaultChargeableService($summary, $defaultCodeId = null, $detach = false)
    {
        return $this->patientSummaryRepo->attachChargeableService($summary, $defaultCodeId, $detach);
    }
    
    public function detachDefaultChargeableService($summary, $defaultCodeId)
    {
        return $this->patientSummaryRepo->detachChargeableService($summary, $defaultCodeId);
    }

    /**
     * Returns a collection containing information about billable patients for a practice for a month.
     *
     * The elements of the collection are:
     *  summaries LengthAwarePaginator
     *  is_closed Boolean
     *
     * @param $practiceId
     * @param Carbon $date
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBillablePatientsForMonth($practiceId, Carbon $date)
    {
        $summaries = $this->billablePatientSummaries($practiceId, $date)->paginate(100);

        $summaries->getCollection()->transform(function ($summary) {
            if (! $summary->actor_id) {
                $summary = $this->patientSummaryRepo->attachChargeableServices($summary);
                $summary = $this->patientSummaryRepo->attachBillableProblems($summary->patient, $summary);
            }

            $summary = $this->patientSummaryRepo->setApprovalStatusAndNeedsQA($summary);

            return ApprovableBillablePatient::make($summary);
        });

        $isClosed = ! ! $summaries->getCollection()->every(function ($summary) {
            return ! ! $summary->actor_id;
        });

        return collect([
            'summaries' => $summaries,
            'is_closed' => $isClosed,
        ]);
    }
}
