<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\ValueObjects;

class VariableRatePay
{
    public float $pay;
    public float $rate;

    /**
     * VariableRatePay constructor.
     */
    public function __construct(float $pay, float $rate)
    {
        $this->pay  = $pay;
        $this->rate = $rate;
    }
}
