<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use App\Constants;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class BHI extends AbstractProcessor
{
    public function code(): string
    {
        return ChargeableService::BHI;
    }

    public function minimumNumberOfCalls(): int
    {
        return 1;
    }

    public function minimumNumberOfProblems(): int
    {
        return 1;
    }

    public function minimumTimeInSeconds(): int
    {
        return Constants::TWENTY_MINUTES_IN_SECONDS;
    }

}
