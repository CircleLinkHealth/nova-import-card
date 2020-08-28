<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingStub;

class MonthlyProcessor implements PatientMonthlyBillingProcessor
{
    public function getServicesForTimeTracker(int $patientId, Carbon $month): PatientChargeableSummaryCollection
    {
        //summary repository
        return new PatientChargeableSummaryCollection();
    }

    public function process(PatientMonthlyBillingStub $patientStub): PatientMonthlyBillingStub
    {
        $patientStub->getAvailableServiceProcessors()
            ->toCollection()
            ->each(function (PatientServiceProcessor $processor) use ($patientStub) {
                if ($processor->shouldAttach(
                    $patientStub->getPatientId(),
                    $patientStub->getChargeableMonth(),
                    ...$patientStub->getPatientProblems()
                )) {
                    $processor->attach($patientStub->getPatientId(), $patientStub->getChargeableMonth());
                }
            });

        return $patientStub;
    }
}
