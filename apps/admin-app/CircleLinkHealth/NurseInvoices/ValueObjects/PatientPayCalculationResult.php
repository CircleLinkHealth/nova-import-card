<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\ValueObjects;

use Illuminate\Support\Collection;

class PatientPayCalculationResult
{
    /** @var ?Collection In case of variable pay payment, array of high rate payments */
    public ?Collection $highRates = null;

    /** @var ?Collection In case of variable pay payment, array of low rate payments */
    public ?Collection $lowRates = null;

    public float $pay;

    /** @var ?Collection In case of visit fee payment, [chargeableServiceCode => [range(key), payment(value)] ] */
    public ?Collection $visitsPerChargeableServiceCode = null;

    /**
     * PatientPayCalculationResult constructor.
     */
    private function __construct()
    {
    }

    public static function withHighLowRates(Collection $highRates, Collection $lowRates)
    {
        $instance            = new self();
        $instance->highRates = $highRates;
        $instance->lowRates  = $lowRates;
        $instance->pay       = $highRates->sum() + $lowRates->sum();

        return $instance;
    }

    public static function withVisits(Collection $visitsPerChargeableServiceCode)
    {
        $instance                                 = new self();
        $instance->visitsPerChargeableServiceCode = $visitsPerChargeableServiceCode;
        $instance->pay                            = $visitsPerChargeableServiceCode
            ->sum(function (Collection $perDay) {
                return $perDay->sum(function (VisitFeePay $item) {
                    return $item->fee;
                });
            });

        return $instance;
    }
}
