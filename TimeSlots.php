<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Nurseinvoices;

class TimeSlots
{
    public int $after20   = 0;
    public int $after30   = 0;
    public int $after40   = 0;
    public int $after60   = 0;
    public int $towards20 = 0;
    public int $towards30 = 0;

    /**
     * TimeSlots constructor.
     */
    public function __construct()
    {
    }
}
