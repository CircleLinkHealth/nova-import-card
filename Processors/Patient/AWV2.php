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
        return 0;
    }

    public function minimumNumberOfProblems(): int
    {
        return 0;
    }

    public function minimumTimeInSeconds(): int
    {
        return 0;
    }

    public function requiresPatientConsent(int $patientId): bool
    {
        return false;
    }
    
    public function featureIsEnabled(): bool
    {
        if (isUnitTestingEnv()){
            return true;
        }
        //todo: use feature toggling (package) in next iteration
        return false;
    }
}
