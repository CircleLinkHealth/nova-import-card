<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use App\Constants;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class RHC extends AbstractProcessor
{
    public function clashesWith(): array
    {
        return [];
    }

    public function code(): string
    {
        return ChargeableService::GENERAL_CARE_MANAGEMENT;
    }

    public function codeForProblems(): string
    {
        return ChargeableService::CCM;
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
        return Constants::TWENTY_MINUTES_IN_SECONDS;
    }

    public function requiresPatientConsent(int $patientId): bool
    {
        return false;
    }
}
