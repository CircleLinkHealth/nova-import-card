<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use CircleLinkHealth\Customer\Entities\ChargeableService;

class G0511 extends AbstractProcessor
{
    public function clashesWith(): array
    {
        return [
            new CCM(),
        ];
    }

    public function code(): string
    {
        return ChargeableService::GENERAL_CARE_MANAGEMENT;
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
        return 1200;
    }

    public function requiresPatientConsent(int $patientId): bool
    {
        return false;
    }
    
    public function featureIsEnabled(): bool
    {
        return true;
    }
}
