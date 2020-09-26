<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Patient;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;

class PatientHasServiceCode
{
    public static function execute(int $patientId, string $serviceCode, $requiresConsent = false): bool
    {
        return ChargeablePatientMonthlySummaryView::where('patient_user_id', $patientId)
            ->where('chargeable_service_code', $serviceCode)
            ->where('chargeable_month', Carbon::now()->startOfMonth())
            ->where('requires_patient_consent', $requiresConsent)
            ->exists();
    }
}
