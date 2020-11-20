<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;

class AWV2 extends AbstractProcessor
{
    public function code(): string
    {
        return ChargeableService::AWV_SUBSEQUENT;
    }

    public function featureIsEnabled(): bool
    {
        return Feature::isEnabled(BillingConstants::AWV_BILLING_FLAG);
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
}
