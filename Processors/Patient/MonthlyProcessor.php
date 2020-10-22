<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;

class MonthlyProcessor implements PatientMonthlyBillingProcessor
{
    public function process(PatientMonthlyBillingDTO $patient): PatientMonthlyBillingDTO
    {
        $patient->getAvailableServiceProcessors()
            ->toCollection()
            ->each(function (PatientServiceProcessor $processor) use ($patient) {
                $processor->processBilling($patient);
            });

        return $patient;
    }
}
