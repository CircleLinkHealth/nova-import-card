<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;

class BHI implements PatientChargeableServiceProcessor
{
    public function attach(int $patientId, Carbon $monthYear)
    {
        // TODO: Implement attach() method.
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
        return 1;
    }

    public function minimumTimeInSeconds(): int
    {
        return 1200;
    }

    public function name(): string
    {
    }

    public function processBilling(Carbon $monthYear)
    {
        // TODO: Implement processBilling() method.
    }

    public function shouldAttach($patientProblems, Carbon $monthYear)
    {
        //patient has at least 1 cpm problem which has BHI CS
    }

    public function shouldFulfill(Carbon $monthYear)
    {
        // TODO: Implement shouldFulfill() method.
    }
}
