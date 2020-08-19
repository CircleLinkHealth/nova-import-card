<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientMonthlyBillingProcessor;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingStub;

class FakeMonthlyBillingProcessor implements PatientMonthlyBillingProcessor
{
    public PatientChargeableSummaryCollection $getServicesForTimeTrackerValue;
    public PatientMonthlyBillingStub $processReturnValue;

    public function getServicesForTimeTracker(int $patientId, Carbon $month): PatientChargeableSummaryCollection
    {
        return $this->getServicesForTimeTrackerValue;
    }

    public function process(PatientMonthlyBillingStub $patientStub): PatientMonthlyBillingStub
    {
        //call each processor -> each processor attaches etc
        return $this->processReturnValue;
    }
}
