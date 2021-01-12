<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;

interface PatientMonthlyBillingProcessor
{
    public function process(PatientMonthlyBillingDTO $patientStub): PatientMonthlyBillingDTO;
}
