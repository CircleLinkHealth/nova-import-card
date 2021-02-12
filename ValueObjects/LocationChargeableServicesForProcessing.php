<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\ValueObjects;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use Illuminate\Database\Eloquent\Collection;

class LocationChargeableServicesForProcessing
{
    public string $code;
    public bool $isLocked;
    public Carbon $month;

    public static function fromCollection(Collection $collection): array
    {
        return $collection->map(function (ChargeableLocationMonthlySummary $summary) {
            $self = new self();
            $self->isLocked = $summary->is_locked;
            $self->code = $summary->chargeableService->code;
            $self->month = $summary->chargeable_month;

            return $self;
        })
            ->toArray();
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMonth(): Carbon
    {
        return $this->month;
    }

    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function setIsLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    public function setMonth(Carbon $month): self
    {
        $this->month = $month;

        return $this;
    }
}
