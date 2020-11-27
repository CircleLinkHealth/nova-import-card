<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;

class BHI extends AbstractProcessor
{
    public function clashesWith(): array
    {
        return [
            new RHC(),
            new RPM(),
            new RPM40(),
        ];
    }

    public function code(): string
    {
        return ChargeableService::BHI;
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
        return CpmConstants::TWENTY_MINUTES_IN_SECONDS;
    }

    public function requiresPatientConsent(int $patientId): bool
    {
        return ! User::hasBhiConsent()
            ->where('id', $patientId)
            ->exists();
    }
}
