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
    protected bool $isDetaching;

    public function __construct(int $patientUserId, int $chargeableServiceId, string $actionType = PatientForcedChargeableService::FORCE_ACTION_TYPE, ?Carbon $month = null, bool $isDetaching)
    {
        $this->patientUserId       = $patientUserId;
        $this->chargeableServiceId = $chargeableServiceId;
        $this->actionType              = $actionType;
        $this->month               = $month;
        $this->isDetaching            = $isDetaching;
    }

    public static function execute(int $patientUserId, int $chargeableServiceId, ?Carbon $month = null, bool $isDetaching): void
    {
        (new static($patientUserId, $chargeableServiceId, PatientForcedChargeableService::FORCE_ACTION_TYPE, $month, $isDetaching))
            ->setForcedChargeableService()
            ->guaranteeHistoricallyAccurateRecords()
            ->reprocessPatientForBilling();
    }

    private function guaranteeHistoricallyAccurateRecords() : self
    {
        self::processHistoricalRecords($this->patientUserId, $this->chargeableServiceId, $this->actionType, $this->month, $this->isDetaching);

        return $this;
    }

    public static function processHistoricalRecords(int $patientUserId, int $chargeableServiceId, string $actionType, ?Carbon $month, bool $isDetaching = false) : void
    {
        $patient = User::ofType('participant')
            ->with('forcedChargeableServices')
            ->findOrFail($patientUserId);

        //if detaching forced CS
        //1. IF detaching perma force or block, create historical records from created_at date until now  (maybe double check for intermediary opposite type entries for month)
        //2. If for specific month just detach
        if ($isDetaching){
            if (is_null($month)){
                $startingMonth = '';//need starting month
            }
            return;
        }


        //iF not detaching
        //
        //if type is force,
        //1.remove block from month if exists
        //2.if perma block, convert to historical records if you must
        //3.if month, check for perma, and log error somewhere if perma exists.

        //if type is block above or opposite
    }



    public static function onPivotModelEvent(int $patientUserId, int $chargeableServiceId, string $actionType, ?Carbon $month, bool $isDetaching):void
    {
        self::processHistoricalRecords($patientUserId, $chargeableServiceId, $actionType, $month, $isDetaching);
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
