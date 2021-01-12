<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location\Stubs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;

class ChargeableLocationMonthlySummaryStub
{
    public ?float $amount;
    public Carbon $chargeable_month;
    public int $chargeable_service_id;
    public int $location_id;

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function getChargeableMonth(): Carbon
    {
        return $this->chargeable_month;
    }

    public function getChargeableServiceId(): int
    {
        return $this->chargeable_service_id;
    }

    public function getLocationId(): int
    {
        return $this->location_id;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function setChargeableMonth(Carbon $chargeable_month): self
    {
        $this->chargeable_month = $chargeable_month;

        return $this;
    }

    public function setChargeableServiceId(int $chargeable_service_id): self
    {
        $this->chargeable_service_id = $chargeable_service_id;

        return $this;
    }

    public function setLocationId(int $location_id): self
    {
        $this->location_id = $location_id;

        return $this;
    }

    public function toArray()
    {
        return [
            'location_id'           => $this->location_id,
            'chargeable_service_id' => $this->chargeable_service_id,
            'chargeable_month'      => $this->chargeable_month,
            'amount'                => $this->amount ?? null,
        ];
    }

    public function toModel(): ChargeableLocationMonthlySummary
    {
        return new ChargeableLocationMonthlySummary($this->toArray());
    }
}
