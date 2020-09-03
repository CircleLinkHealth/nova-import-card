<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location\Stubs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;

class ChargeableLocationMonthlySummaryStub
{
    public float $amount;
    public Carbon $chargeable_month;
    public int $chargeable_service_id;
    public int $location_id;

    public function __construct(int $location_id, int $chargeable_service_id, Carbon $chargeable_month, float $amount)
    {
        $this->location_id           = $location_id;
        $this->chargeable_service_id = $chargeable_service_id;
        $this->chargeable_month      = $chargeable_month;
        $this->amount                = $amount;
    }

    public function toArray()
    {
        return [
            'location_id'           => $this->location_id,
            'chargeable_service_id' => $this->chargeable_service_id,
            'chargeable_month'      => $this->chargeable_month,
            'amount'                => $this->amount,
        ];
    }

    public function toModel(): ChargeableLocationMonthlySummary
    {
        return new ChargeableLocationMonthlySummary($this->toArray());
    }
}
