<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\Customer\Entities\User;

class ForcePatientChargeableService
{
    protected int $chargeableServiceId;
    protected string $actionType;
    protected ?Carbon $month;
    protected int $patientUserId;

    public function __construct(int $patientUserId, int $chargeableServiceId, string $actionType = PatientForcedChargeableService::FORCE_ACTION_TYPE, ?Carbon $month = null)
    {
        $this->patientUserId       = $patientUserId;
        $this->chargeableServiceId = $chargeableServiceId;
        $this->actionType              = $actionType;
        $this->month               = $month;
    }

    public static function force(int $patientUserId, int $chargeableServiceId, ?Carbon $month = null): void
    {
        (new static($patientUserId, $chargeableServiceId, PatientForcedChargeableService::FORCE_ACTION_TYPE, $month))
            ->guaranteeHistoricallyAccurateRecords()
            ->setForcedChargeableService()
            ->reprocessPatientForBilling();
    }

    public static function block(int $patientUserId, int $chargeableServiceId, ?Carbon $month = null): void
    {
        (new static($patientUserId, $chargeableServiceId, PatientForcedChargeableService::BLOCK_ACTION_TYPE, $month))
            ->guaranteeHistoricallyAccurateRecords()
            ->setForcedChargeableService()
            ->reprocessPatientForBilling();
    }

    private function guaranteeHistoricallyAccurateRecords() : self
    {
        //get all records

        //
        return $this;
    }

    private function reprocessPatientForBilling(): void
    {
        BillingCache::clearPatients([$this->patientUserId]);
        (app(ProcessPatientSummaries::class))->execute($this->patientUserId, $this->month);
    }

    private function setForcedChargeableService(): self
    {
        User::ofType('participant')
            ->findOrFail($this->patientUserId)
            ->forcedChargeableServices()
            ->syncWithPivotValues([
                $this->chargeableServiceId,
            ], [
                'action_type'        => $this->actionType,
                'chargeable_month' => $this->month,
            ], false);

        return $this;
    }
}
