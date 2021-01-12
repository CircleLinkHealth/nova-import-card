<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\ValueObjects;

class VisitFeePay
{
    public float $count;
    public float $fee;
    public ?string $lastLogDate;

    /**
     * VisitFeePay constructor.
     */
    public function __construct(?string $lastLogDate, float $fee, float $count)
    {
        $this->lastLogDate = $lastLogDate;
        $this->fee         = $fee;
        $this->count       = $count;
    }
}
