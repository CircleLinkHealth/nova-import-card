<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class PCM extends AbstractProcessor
{
    public function clashesWith(): array
    {
        return [
            new CCM(),
            new RHC(),
            new RPM(),
            new RPM40(),
            new RPM60(),
        ];
    }

    public function code(): string
    {
        return ChargeableService::PCM;
    }

    public function featureIsEnabled(): bool
    {
        return true;
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
        return CpmConstants::THIRTY_MINUTES_IN_SECONDS;
    }

    public function requiresPatientConsent(int $patientId): bool
    {
        return false;
    }
}
