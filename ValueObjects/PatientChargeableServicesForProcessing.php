<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;

//todo: make name singular
class PatientChargeableServicesForProcessing
{
    protected int $chargeableServiceId;
    protected string $code;
    protected bool $isFulfilled;

    protected int $monthlyTime = 0;
    protected bool $requiresConsent;

    public static function fromCollection(User $patient): array
    {
        $services    = $patient->chargeableMonthlySummaries;
        $monthlyTime = $patient->chargeableMonthlyTime;

        return $services->map(function (ChargeablePatientMonthlySummary $summary) use ($monthlyTime) {
            return (new self())->setCode($summary->chargeableService->code)
                ->setChargeableServiceId($summary->chargeable_service_id)
                ->setIsFulfilled($summary->is_fulfilled)
                ->setRequiresConsent($summary->requires_patient_consent)
                ->setMonthlyTime(
                    optional(
                        $monthlyTime->where(
                            'chargeable_service_id',
                            $summary->chargeable_service_id
                        )
                            ->where('chargeable_month', $summary->chargeable_month)
                            ->first()
                    )->total_time
                );
        })
            ->filter()
            ->toArray();
    }

    public function getChargeableServiceId(): int
    {
        return $this->chargeableServiceId;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMonthlyTime(): int
    {
        return $this->monthlyTime;
    }

    public function isFulfilled(): bool
    {
        return $this->isFulfilled;
    }

    public function requiresConsent(): bool
    {
        return $this->requiresConsent;
    }

    public function setChargeableServiceId(int $chargeableServiceId): self
    {
        $this->chargeableServiceId = $chargeableServiceId;

        return $this;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function setIsFulfilled(bool $isFulfilled): self
    {
        $this->isFulfilled = $isFulfilled;

        return $this;
    }

    /**
     * @param int $monthlyTime
     */
    public function setMonthlyTime(?int $monthlyTime = null): self
    {
        $this->monthlyTime = $monthlyTime ?? 0;

        return $this;
    }

    public function setRequiresConsent(bool $requiresConsent): self
    {
        $this->requiresConsent = $requiresConsent;

        return $this;
    }
}
