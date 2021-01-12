<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Stubs;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class IsFulfilledStub implements Arrayable
{
    public string $chargeableServiceCode;
    public Carbon $month;
    public int $patientId;
    public bool $showAsFulfilled;

    public function __construct(int $patientId, string $chargeableServiceCode, Carbon $month, bool $showAsFulfilled)
    {
        $this->patientId             = $patientId;
        $this->chargeableServiceCode = $chargeableServiceCode;
        $this->month                 = $month;
        $this->showAsFulfilled       = $showAsFulfilled;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'chargeableServiceCode' => $this->chargeableServiceCode,
            'month'                 => $this->month,
            'patientId'             => $this->patientId,
            'showAsFulfilled'       => $this->showAsFulfilled,
        ];
    }
}
