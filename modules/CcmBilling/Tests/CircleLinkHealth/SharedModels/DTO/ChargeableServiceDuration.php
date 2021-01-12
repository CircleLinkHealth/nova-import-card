<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\DTO;

class ChargeableServiceDuration
{
    public int $duration;
    public ?int $id;

    /**
     * For background compatibility.
     */
    public bool $isBehavioral;

    /**
     * ChargeableServiceDuration constructor.
     */
    public function __construct(?int $id, int $duration, bool $isBehavioral = false)
    {
        $this->id           = $id;
        $this->isBehavioral = $isBehavioral;
        $this->duration     = $duration;
    }
}
