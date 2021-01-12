<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\ValueObjects;

class PatientChargeableServiceForTimeTracker
{
    public int $chargeableServiceId;

    public int $time;

    /**
     * PatientChargeableServiceForTimeTracker constructor.
     */
    public function __construct(int $chargeableServiceId, int $time)
    {
        $this->chargeableServiceId = $chargeableServiceId;
        $this->time                = $time;
    }

    public function toArray()
    {
        return [
            'chargeable_service_id' => $this->chargeableServiceId,
            'time'                  => $this->time,
        ];
    }
}
