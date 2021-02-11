<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;


use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\Customer\Entities\User;

class PatientChargeableServicesForProcessing
{
    protected string $code;
    protected bool $isFulfilled;
    protected bool $requiresConsent;

    public static function fromCollection(User $patient): array
    {
        $services    = $patient->chargeableMonthlySummaries;
        $monthlyTime = $patient->chargeableMonthlyTime;

        return $services->map(function (ChargeablePatientMonthlySummary $summary) use ($monthlyTime) {
            return (new self())->setCode($code = $summary->chargeableService->code)
                               ->setIsFulfilled($summary->is_fulfilled)
                               ->setRequiresConsent($summary->requires_patient_consent)
                               ->setMonthlyTime(
                                   optional($monthlyTime->where('chargeable_service_id',
                                       $summary->chargeable_service_id)
                                                        ->where('chargeable_month', $summary->chargeable_month)
                                                        ->first()
                                       ->time)
                               );
        })
                        ->filter()
                        ->toArray();
    }

    public static function fromRelationship(PatientForcedChargeableService $s): ?self
    {
        if ( ! $s->chargeableService) {
            return null;
        }

        return (new static())
            ->setChargeableServiceCode($s->chargeableService->code)
            ->setActionType($s->action_type);
    }

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
    public function setRequiresConsent(bool $requiresConsent): self
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
    public function setCode(string $code): self
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
    public function setIsFulfilled(bool $isFulfilled): self
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
    public function setMonthlyTime(?int $monthlyTime = null): self
    {
        $this->monthlyTime = $monthlyTime ?? 0;
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