<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;

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

    public function store(int $patientId, int $chargeableServiceId, Carbon $month): ChargeablePatientMonthlySummary
    {
        return ChargeablePatientMonthlySummary::updateOrCreate([
            'patient_user_id'  => $patientId,
            'chargeable_month' => $chargeableServiceId,
        ]);
    }
}
