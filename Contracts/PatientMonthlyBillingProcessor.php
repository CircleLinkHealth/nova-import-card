<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
use CircleLinkHealth\CcmBilling\ValueObjects\PatientMonthlyBillingDTO;

interface PatientMonthlyBillingProcessor
{
    public function getServicesForTimeTracker(int $patientId, Carbon $month): PatientChargeableSummaryCollection;

    public function process(PatientMonthlyBillingDTO $patientStub): PatientMonthlyBillingDTO;
}
