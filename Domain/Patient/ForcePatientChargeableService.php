<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;


use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\Customer\Entities\User;

class ForcePatientChargeableService
{
    protected int $patientUserId;
    protected int $chargeableServiceId;
    protected bool $force;
    protected ?Carbon $month;

    public function __construct(int $patientUserId, int $chargeableServiceId, bool $force = true, ?Carbon $month = null)
    {
        $this->patientUserId       = $patientUserId;
        $this->chargeableServiceId = $chargeableServiceId;
        $this->force               = $force;
        $this->month               = $month;
    }

    public static function force(int $patientUserId, int $chargeableServiceId, ?Carbon $month = null): void
    {
        (new static($patientUserId, $chargeableServiceId, true, $month))
            ->setForcedChargeableService()
            ->reprocessPatientForBilling();
    }

    public static function unForce(int $patientUserId, int $chargeableServiceId, ?Carbon $month = null): void
    {
        (new static($patientUserId, $chargeableServiceId, false, $month))
            ->setForcedChargeableService()
            ->reprocessPatientForBilling();
    }

    private function setForcedChargeableService(): self
    {
        User::ofType('participant')
            ->findOrFail($this->patientUserId)
            ->forcedChargeableServices()
            ->syncWithPivotValues([
                $this->chargeableServiceId,
            ], [
                'is_forced'        => $this->force,
                'chargeable_month' => $this->month,
            ], false);

        return $this;
    }

    private function reprocessPatientForBilling(): void
    {
        BillingCache::clearPatients([$this->patientUserId]);
        (app(ProcessPatientSummaries::class))->execute($this->patientUserId, $this->month);
    }
}