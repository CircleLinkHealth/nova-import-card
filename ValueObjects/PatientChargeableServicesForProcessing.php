<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;


class PatientChargeableServicesForProcessing
{
    protected string $code;
    protected bool $isFulfilled;
    protected bool $requiresConsent;

    /**
     * @return bool
     */
    public function requiresConsent(): bool
    {
        return $this->requiresConsent;
    }

    /**
     * @param bool $requiresConsent
     */
    public function setRequiresConsent(bool $requiresConsent): void
    {
        $this->requiresConsent = $requiresConsent;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function isFulfilled(): bool
    {
        return $this->isFulfilled;
    }

    /**
     * @param bool $isFulfilled
     */
    public function setIsFulfilled(bool $isFulfilled): void
    {
        $this->isFulfilled = $isFulfilled;
    }

    /**
     * @return int
     */
    public function getMonthlyTime(): int
    {
        return $this->monthlyTime;
    }

    /**
     * @param int $monthlyTime
     */
    public function setMonthlyTime(int $monthlyTime): void
    {
        $this->monthlyTime = $monthlyTime;
    }

//    Will contain below:
//        /** @var ChargeablePatientMonthlyTime $monthlyTime */
//        $monthlyTime = $patient
//            ->chargeableMonthlyTime
//            ->where('chargeableService.code', $this->baseCode())
//            ->where('chargeable_month', $chargeableMonth)
//            ->first();
//
//        if ( ! $monthlyTime) {
//            return false;
//        }
    protected int $monthlyTime;
}