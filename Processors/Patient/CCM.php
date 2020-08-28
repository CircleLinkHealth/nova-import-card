<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use App\Constants;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;
use CircleLinkHealth\CcmBilling\Traits\PropagatesSequence;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class CCM extends AbstractProcessor
{
    use PropagatesSequence;

    public function code(): string
    {
        return ChargeableService::CCM;
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
        return Constants::TWENTY_MINUTES_IN_SECONDS;
    }

    public function next(): PatientChargeableServiceProcessor
    {
        return new CCM40();
    }
}
