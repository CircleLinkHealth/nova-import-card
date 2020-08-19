<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\CcmBilling\Contracts;

use Carbon\Carbon;
use Modules\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;
use Modules\CcmBilling\ValueObjects\PatientMonthlyBillingStub;

interface PatientMonthlyBillingProcessor
{
    public function getServicesForTimeTracker(int $patientId, Carbon $month): PatientChargeableSummaryCollection;

    public function process(PatientMonthlyBillingStub $patientStub): PatientMonthlyBillingStub;
}
