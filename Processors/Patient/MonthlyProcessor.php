<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;

class MonthlyProcessor implements PatientMonthlyBillingProcessor
{
    public function getServicesForTimeTracker(int $patientId, Carbon $month): PatientChargeableSummaryCollection
    {
        //todo: summary repository (address this)
        return new PatientChargeableSummaryCollection();
    }

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
