<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class RHC extends AbstractProcessor
{
    public function baseCode(): string
    {
        return ChargeableService::CCM;
    }

    public function clashesWith(): array
    {
        return [];
    }

    public function code(): string
    {
        return ChargeableService::GENERAL_CARE_MANAGEMENT;
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
        return 2;
    }

    public function minimumTimeInSeconds(): int
    {
        return CpmConstants::TWENTY_MINUTES_IN_SECONDS;
    }

    public function requiresPatientConsent(int $patientId): bool
    {
        return false;
    }
}
