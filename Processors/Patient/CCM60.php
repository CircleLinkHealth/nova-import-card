<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;

class CCM60 implements PatientChargeableServiceProcessor
{
    public function attach(int $patientId, Carbon $monthYear): ChargeablePatientMonthlySummary
    {
        return $this->repo()->store($patientId, $this->code(), $monthYear);
    }

    public function fulfill(Carbon $monthYear)
    {
        // TODO: Implement fulfill() method.
    }

    public function isAttached(Carbon $monthYear)
    {
        // TODO: Implement isAttached() method.
    }

    public function isFulfilled(Carbon $monthYear)
    {
        // TODO: Implement isFulfilled() method.
    }

    public function minimumNumberOfCalls(): int
    {
        // TODO: Implement minimumNumberOfCalls() method.
    }

    public function minimumTimeInSeconds(): int
    {
        // TODO: Implement minimumTimeInSeconds() method.
    }

    public function name(): string
    {
        // TODO: Implement name() method.
    }

    public function processBilling(Carbon $monthYear)
    {
        // TODO: Implement processBilling() method.
    }

    public function shouldAttach($patientProblems, Carbon $monthYear)
    {
        // TODO: Implement shouldAttach() method.
    }

    public function shouldFulfill(Carbon $monthYear)
    {
        // TODO: Implement shouldFulfill() method.
    }
}
