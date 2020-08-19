<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Traits\PropagatesSequence;

class CCM40 implements PatientChargeableServiceProcessor
{
    use PropagatesSequence;

    public function attach(int $patientId, Carbon $monthYear): ChargeablePatientMonthlySummary
    {
        // TODO: Implement attach() method.
    }

    public function code()
    {
        // TODO: Implement code() method.
    }

    public function fulfill(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement fulfill() method.
    }

    public function isAttached(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement isAttached() method.
    }

    public function isFulfilled(int $patientId, Carbon $monthYear)
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

    public function next(): PatientChargeableServiceProcessor
    {
        // TODO: Implement next() method.
    }

    public function processBilling(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement processBilling() method.
    }

    public function shouldAttach($patientProblems, Carbon $monthYear)
    {
        // TODO: Implement shouldAttach() method.
    }

    public function shouldFulfill(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement shouldFulfill() method.
    }
}
