<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Services;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\ForcePatientChargeableService;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\Processors\Customer\Practice as PracticeProcessor;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsCountForMonthDTO;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsForMonthDTO;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class ApproveBillablePatientsServiceV3
{
    private PracticeProcessor $practiceProcessor;

    public function __construct(PracticeProcessor $practiceProcessor)
    {
        $this->practiceProcessor = $practiceProcessor;
    }

    public function closeMonth(int $actorId, int $practiceId, Carbon $month)
    {
        return $this->practiceProcessor->closeMonth($actorId, $practiceId, $month);
    }

    public function counts(int $practiceId, Carbon $month): BillablePatientsCountForMonthDTO
    {
        return $this->practiceProcessor->counts($practiceId, $month);
    }

    public function getBillablePatientsForMonth($practiceId, Carbon $date): BillablePatientsForMonthDTO
    {
        $pagination     = AppConfig::pull('abp-pagination-size', 20);
        $date           = $date->copy()->startOfMonth();
        $jsonCollection = $this->practiceProcessor->fetchApprovablePatients($practiceId, $date, $pagination);
        $isClosed       = (bool) $jsonCollection->collection->every(
            function ($summary) {
                return (bool) $summary->actor_id;
            }
        );

        return new BillablePatientsForMonthDTO($jsonCollection->resource, $isClosed);
    }

    public function openMonth(int $practiceId, Carbon $month)
    {
        return $this->practiceProcessor->openMonth($practiceId, $month);
    }

    public function setPatientChargeableServices(int $reportId, array $csIds, Carbon $month = null)
    {
        //todo: Modify Patient Activity
        $billingStatus = PatientMonthlyBillingStatus::find($reportId);
        if ( ! $billingStatus) {
            return null;
        }

        $patientId     = $billingStatus->patient_user_id;
        $coll          = collect($csIds);
        $bhiId         = ChargeableService::cached()->firstWhere('code', '=', ChargeableService::BHI)->id;
        $indefiniteIds = $coll->reject(fn ($id) => $id == $bhiId);
        $indefiniteIds->each(fn ($id) => ForcePatientChargeableService::force($patientId, $id));
        if ($coll->has($bhiId)) {
            ForcePatientChargeableService::force(
                $patientId,
                $bhiId,
                $month ?? now()->subMonth()->startOfMonth()
            );
        }
    }
}
