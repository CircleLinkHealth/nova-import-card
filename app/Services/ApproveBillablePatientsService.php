<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

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
    
    public function attachDefaultChargeableService($summary, $defaultCodeId = null, $detach = false)
    {
        return $this->patientSummaryRepo->attachChargeableService($summary, $defaultCodeId, $detach);
    }
    
    public function billablePatientSummaries($practiceId, Carbon $month)
    {
        return $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month);
    }
    
    public function counts($practiceId, Carbon $month)
    {
        $count['approved'] = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month, true)
            ->where('approved', '=', true)
            ->where('rejected', '=', false)
            ->count();
        
        $count['toQA'] = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month, true)
            ->where('approved', '=', false)
            ->where('rejected', '=', false)
            ->where('needs_qa', '=', true)
            ->count();
        
        $count['rejected'] = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month, true)
            ->where('rejected', '=', true)
            ->where('approved', '=', false)
            ->count();
        
        return $count;
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
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBillablePatientsForMonth($practiceId, Carbon $date)
    {
        // 1. this will fetch billable patients that have
        //    ccm > 1200 and/or bhi > 1200
        $summaries = $this->billablePatientSummaries($practiceId, $date)->paginate(30);
        
        // 2. if patient is only eligible for PCM, we should make sure ccm time is over 30 mins
        //    if patient is eligible for both PCM and CCM we choose CCM
        $summaries->getCollection()->transform(
            function ($summary) {
                if ( ! $summary->actor_id) {
                    $summary = $this->patientSummaryRepo->setApprovalStatusAndNeedsQA(
                        $this->patientSummaryRepo->attachChargeableServices($summary)
                    );
                }
                
                return ApprovableBillablePatient::make($summary);
            }
        );
        
        $isClosed = (bool) $summaries->getCollection()->every(
            function ($summary) {
                return (bool) $summary->actor_id;
            }
        );
        
        return collect(
            [
                'summaries' => $summaries,
                'is_closed' => $isClosed,
            ]
        );
    }
}
