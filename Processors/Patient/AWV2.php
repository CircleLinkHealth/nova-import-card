<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use CircleLinkHealth\Customer\Entities\ChargeableService;

class AWV2 extends AbstractProcessor
{
    public function code(): string
    {
        return ChargeableService::AWV_SUBSEQUENT;
    }

    public function minimumNumberOfCalls(): int
    {
        // TODO: Implement minimumNumberOfCalls() method.
    }

    public function minimumNumberOfProblems(): int
    {
        // TODO: Implement minimumNumberOfProblems() method.
    }

    public function minimumTimeInSeconds(): int
    {
        // TODO: Implement minimumTimeInSeconds() method.
    }
}
