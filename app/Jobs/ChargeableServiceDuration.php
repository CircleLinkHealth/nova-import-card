<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

class ChargeableServiceDuration
{
    public int $duration;
    public ?int $id;

    /**
     * ChargeableServiceDuration constructor.
     */
    public function __construct(?int $id, int $duration)
    {
        $this->id       = $id;
        $this->duration = $duration;
    }
}
