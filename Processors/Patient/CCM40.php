<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Traits\PropagatesSequence;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class CCM40 extends AbstractProcessor
{
    use PropagatesSequence;

    public function code(): string
    {
        return ChargeableService::CCM_PLUS_40;
    }

    public function minimumNumberOfCalls(): int
    {
        return 1;
    }

    public function minimumNumberOfProblems(): int
    {
        return 2;
    }

    public function minimumTimeInSeconds(): int
    {
        return 2400;
    }

    public function next(): PatientServiceProcessor
    {
        return new CCM60();
    }
}
