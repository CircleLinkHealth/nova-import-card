<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient\Stubs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use Illuminate\Contracts\Support\Arrayable;

class ChargeablePatientMonthlySummaryStub implements Arrayable
{
    public string $chargeableServiceCode;
    public Carbon $month;
    public int $patientId;

    private ChargeablePatientMonthlySummary $summary;

    public function __construct(int $patientId, string $chargeableServiceCode, Carbon $month, ChargeablePatientMonthlySummary $summary)
    {
        $this->patientId             = $patientId;
        $this->chargeableServiceCode = $chargeableServiceCode;
        $this->month                 = $month;
        $this->summary               = $summary;
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
            'summary'               => $this->summary,
        ];
    }
}
