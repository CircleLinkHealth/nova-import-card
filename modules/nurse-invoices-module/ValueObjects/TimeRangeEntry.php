<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\ValueObjects;

class TimeRangeEntry
{
    public int $duration;
    public bool $hasSuccessfulCall;
    public ?string $lastLogDate;

    /**
     * TimeRangeEntry constructor.
     */
    public function __construct(int $duration, bool $hasSuccessfulCall, ?string $lastLogDate)
    {
        $this->duration          = $duration;
        $this->hasSuccessfulCall = $hasSuccessfulCall;
        $this->lastLogDate       = $lastLogDate;
    }
}
