<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class RPM60 extends AbstractProcessor
{
    public function baseCode(): string
    {
        return ChargeableService::RPM;
    }

    //todo: change fulfillment to happen with base service time
    public function clashesWith(): array
    {
        return [
            new RHC(),
        ];
    }

    public function code(): string
    {
        return ChargeableService::RPM60;
    }

    public function featureIsEnabled(): bool
    {
        return true;
    }

    //TODO: remove call counts for here not needed
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
        return CpmConstants::SIXTY_MINUTES_IN_SECONDS;
    }

    public function requiresPatientConsent(int $patientId): bool
    {
        return false;
    }
}
