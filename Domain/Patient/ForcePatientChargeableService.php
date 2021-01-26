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

    public static function execute(int $patientUserId, int $chargeableServiceId, ?Carbon $month = null): void
    {
        (new static($patientUserId, $chargeableServiceId, PatientForcedChargeableService::FORCE_ACTION_TYPE, $month))
            ->setForcedChargeableService()
            ->guaranteeHistoricallyAccurateRecords()
            ->reprocessPatientForBilling();
    }

    private function guaranteeHistoricallyAccurateRecords() : self
    {
        self::processHistoricalRecords($this->patientUserId, $this->chargeableServiceId, $this->actionType, $this->month);

        return $this;
    }

    public static function processHistoricalRecords(int $patientUserId, int $chargeableServiceId, string $actionType, ?Carbon $month) : void
    {
        $patient = User::ofType('participant')
            ->with('forcedChargeableServices')
            ->findOrFail($patientUserId);

    }



    public static function onPivotModelEvent(int $patientUserId, int $chargeableServiceId, string $actionType, ?Carbon $month):void
    {
        self::processHistoricalRecords($patientUserId, $chargeableServiceId, $actionType, $month);
        ProcessPatientSummaries::wipeAndReprocessForMonth(
            $patientUserId,
            is_null($month) ? Carbon::now()->startOfMonth() : $month
        );
    }


    private function reprocessPatientForBilling(): void
    {
        ProcessPatientSummaries::wipeAndReprocessForMonth(
            $this->patientUserId,
            is_null($this->month) ? Carbon::now()->startOfMonth() : $this->month
        );
    }

    private function setForcedChargeableService(): self
    {
        //don't sync, attach
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
