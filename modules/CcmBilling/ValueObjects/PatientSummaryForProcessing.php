<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class PatientSummaryForProcessing
{
    protected int $chargeableServiceId;
    protected string $code;
    protected bool $isFulfilled;
    protected bool $requiresConsent;
    protected string $displayName;

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     *
     * @return PatientSummaryForProcessing
     */
    public function setDisplayName(string $displayName): PatientSummaryForProcessing
    {
        $this->displayName = $displayName;

        return $this;
    }

    public static function fromCollection(EloquentCollection $summaries): array
    {
        return $summaries->map(function (ChargeablePatientMonthlySummary $summary) {
            return (new self())->setCode($summary->chargeableService->code)
                ->setDisplayName($summary->chargeableService->display_name)
                ->setChargeableServiceId($summary->chargeable_service_id)
                ->setIsFulfilled($summary->is_fulfilled)
                ->setRequiresConsent($summary->requires_patient_consent);
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

    public function setRequiresConsent(bool $requiresConsent): self
    {
        $this->requiresConsent = $requiresConsent;

        return $this;
    }
}
