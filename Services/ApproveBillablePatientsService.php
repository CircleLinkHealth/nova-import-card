<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Services;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Http\Resources\ApprovableBillablePatient;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsCountForMonthDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsForMonthDTO;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\SharedModels\Repositories\BillablePatientsEloquentRepository;
use CircleLinkHealth\SharedModels\Repositories\PatientSummaryEloquentRepository;

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

    public function counts($practiceId, Carbon $month): BillablePatientsCountForMonthDTO
    {
        // the counts might be inaccurate here because the records might
        // not be processed yet. see command ProcessApprovableBillablePatientSummary

        $approved = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month, true)
            ->where('approved', '=', true)
            ->where('rejected', '=', false)
            ->count();

        $toQA = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month, true)
            ->where('approved', '=', false)
            ->where('rejected', '=', false)
            ->where('needs_qa', '=', true)
            ->count();

        $rejected = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month, true)
            ->where('rejected', '=', true)
            ->where('approved', '=', false)
            ->count();

        // 1. not all fields might have been set, because they might not have been processed yet
        // 2. or we have an actor_id but none of these is true
        $other = $this->approvePatientsRepo
            ->billablePatientSummaries($practiceId, $month, true)
            ->where('rejected', '=', false)
            ->where('approved', '=', false)
            ->where('needs_qa', '=', false)
            ->count();

        return new BillablePatientsCountForMonthDTO($approved, $toQA, $rejected, $other);
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
    public function getBillablePatientsForMonth($practiceId, Carbon $date): BillablePatientsForMonthDTO
    {
        // 1. this will fetch billable patients that have
        //    ccm > 1200 and/or bhi > 1200
        $summaries = $this->billablePatientSummaries($practiceId, $date)->paginate(AppConfig::pull('abp-pagination-size', 20));

        //note: this only applies to the paginated results, not the whole collection. not sure if intended
        $summaries->getCollection()->transform(
            function ($summary) {
                if ( ! $summary->actor_id) {
                    $aSummary = $this->patientSummaryRepo->attachChargeableServices($summary);
                    $summary = $this->patientSummaryRepo->setApprovalStatusAndNeedsQA($aSummary);
                }

                return ApprovableBillablePatient::make($summary);
            }
        );

        $isClosed = (bool) $summaries->getCollection()->every(
            function ($summary) {
                return (bool) $summary->actor_id;
            }
        );

        return new BillablePatientsForMonthDTO($summaries, $isClosed);
    }
}
