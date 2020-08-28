<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class G0511 extends AbstractProcessor
{
    public function code(): string
    {
        return ChargeableService::G0511;
    }

    public function minimumNumberOfCalls(): int
    {
        return 1;
    }

    public function minimumTimeInSeconds(): int
    {
        return 1200;
    }
    
    public function minimumNumberOfProblems(): int
    {
        return 2;
    }
}
