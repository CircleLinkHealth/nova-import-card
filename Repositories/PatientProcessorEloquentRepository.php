<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use Modules\CcmBilling\Http\Resources\PatientChargeableSummaryCollection;

class PatientProcessorEloquentRepository
{
    public function getChargeablePatientSummaries(int $patientId, Carbon $month)
    {
        return ChargeablePatientMonthlySummary::with(['chargeableService' => function ($cs) {
            $cs->select(['id', 'display_name']);
        }])
            ->where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->get();
    }
}
