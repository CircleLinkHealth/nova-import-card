<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;


use App\Constants;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class RPM40 extends AbstractProcessor
{
    
    public function requiresPatientConsent(int $patientId): bool
    {
        return false;
    }
    
    public function code(): string
    {
        return ChargeableService::RPM40;
    }
    
    public function codeForProblems(): string
    {
        return ChargeableService::RPM;
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
        return Constants::TWENTY_MINUTES_IN_SECONDS;
    }
}