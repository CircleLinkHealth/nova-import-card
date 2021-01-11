<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessor;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;

class FakeMonthlyBillingProcessor implements PatientMonthlyBillingProcessor
{
    public PatientChargeableSummaryCollection $getServicesForTimeTrackerValue;
    public PatientMonthlyBillingDTO $processReturnValue;

    public function getServicesForTimeTracker(int $patientId, Carbon $month): PatientChargeableSummaryCollection
    {
        return $this->getServicesForTimeTrackerValue;
    }

    public function process(PatientMonthlyBillingDTO $patientStub): PatientMonthlyBillingDTO
    {
        $patientStub->getAvailableServiceProcessors()
            ->toCollection()
            ->each(function (PatientServiceProcessor $processor) use ($patientStub) {
                if ($processor->shouldAttach(
                    $patientStub->getPatientId(),
                    $patientStub->getChargeableMonth(),
                    $patientStub->getPatientProblems()
                )
                ) {
                    $processor->attach($patientStub->getPatientId(), $patientStub->getChargeableMonth());
                }
            });
    }
}
