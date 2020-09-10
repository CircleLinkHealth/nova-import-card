<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Traits\IsPartOfSequence;
use CircleLinkHealth\Customer\Entities\ChargeableService;

class CCM60 extends AbstractProcessor
{
    use IsPartOfSequence;

    public function code(): string
    {
        return ChargeableService::CCM_PLUS_60;
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
        return 3600;
    }

    public function next(): ?PatientServiceProcessor
    {
        return null;
    }

    public function previous(): ?PatientServiceProcessor
    {
        return new CCM40();
    }
    
    public function requiresPatientConsent(int $patientId): bool
    {
        return false;
    }
}
