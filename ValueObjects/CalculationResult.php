<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\ValueObjects;

use Illuminate\Support\Collection;

class CalculationResult
{
    /** @var bool Option 1 (alt algo - visit fee based if true, Option 2 otherwise */
    public $altAlgoEnabled;
    /** @var bool New CCM Plus Algo from Jan 2020 */
    public $ccmPlusAlgoEnabled;

    /** @var float Total pay */
    public float $totalPay;

    /** @var float Indicates number of visits */
    public float $visitsCount;

    /** @var Collection A matrix array, key[patient id] => [ [chargeable_service_code => [range => pay] ] ]. */
    public Collection $visitsPerPatientPerChargeableServiceCode;

    public function __construct(
        $ccmPlusAlgoEnabled,
        $visitFeeBased,
        Collection $visitsPerPatientPerChargeableServiceCode,
        float $totalPay
    ) {
        $this->ccmPlusAlgoEnabled                       = $ccmPlusAlgoEnabled;
        $this->altAlgoEnabled                           = $visitFeeBased;
        $this->visitsPerPatientPerChargeableServiceCode = $visitsPerPatientPerChargeableServiceCode;
        $this->visitsCount                              = $visitsPerPatientPerChargeableServiceCode
            ->sum(function (Collection $perPatient) {
                return $perPatient->sum(function ($perChargeableServiceCode) {
                    return $perChargeableServiceCode->sum(function (VisitFeePay $perDay) {
                        return $perDay->count;
                    });
                });
            });

        $this->totalPay = $totalPay;
    }
}
