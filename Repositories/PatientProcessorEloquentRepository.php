<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use App\Http\Resources\ChargeableService;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository as Repository;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use Illuminate\Support\Facades\Cache;

class PatientProcessorEloquentRepository implements Repository
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

    public function store(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        return ChargeablePatientMonthlySummary::firstOrCreate([
            'patient_user_id'       => $patientId,
            'chargeable_month'      => $month,
            'chargeable_service_id' => $this->chargeableSercviceId($chargeableServiceCode),
        ]);
    }
    
    public function fulfill(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        return ChargeablePatientMonthlySummary::updateOrCreate([
            'patient_user_id'       => $patientId,
            'chargeable_month'      => $month,
            'chargeable_service_id' => $this->chargeableSercviceId($chargeableServiceCode),
        ], [
            'is_fulfilled' => true
        ]);
    }

    private function chargeableSercviceId(string $code): int
    {
        return Cache::remember("name:chargeable_service_$code", 2, function () use ($code) {
            return ChargeableService::where('code', $code)
                ->value('id');
        });
    }
}
