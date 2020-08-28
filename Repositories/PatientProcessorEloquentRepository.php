<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use App\Http\Resources\ChargeableService;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository as Repository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\ChargeableService as ChargeableServiceModel;
use CircleLinkHealth\Customer\Entities\Patient as PatientModel;
use Illuminate\Support\Facades\Cache;

class PatientProcessorEloquentRepository implements Repository
{
    public function fulfill(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        return ChargeablePatientMonthlySummary::updateOrCreate([
            'patient_user_id'       => $patientId,
            'chargeable_month'      => $month,
            'chargeable_service_id' => $this->chargeableSercviceId($chargeableServiceCode),
        ], [
            'is_fulfilled' => true,
        ]);
    }

    public function getChargeablePatientSummaries(int $patientId, Carbon $month)
    {
        return ChargeablePatientMonthlySummary::with(['chargeableService' => function ($cs) {
            $cs->select(['id', 'display_name']);
        }])
            ->where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->get();
    }

    public function isChargeableServiceEnabledForLocationForMonth(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return ChargeableLocationMonthlySummary::where('chargeable_month', $month)
            ->whereIn('chargeable_service_id', function ($q) use ($chargeableServiceCode) {
                $q->select('id')
                    ->from((new ChargeableServiceModel())->getTable())
                    ->where('code', $chargeableServiceCode);
            })
            ->whereIn('location_id', function ($q) use ($patientId) {
                $q->select('preferred_contact_location')
                    ->from((new PatientModel())->getTable())
                    ->where('user_id', $patientId);
            })->exists();
    }

    public function isFulfilled(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return ChargeablePatientMonthlySummary::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->where('chargeable_service_id', $this->chargeableSercviceId($chargeableServiceCode))
            ->where('is_fulfilled', true)
            ->exists();
    }
    
    public function isAttached(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return ChargeablePatientMonthlySummary::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->where('chargeable_service_id', $this->chargeableSercviceId($chargeableServiceCode))
            ->exists();
    }

    public function store(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        return ChargeablePatientMonthlySummary::firstOrCreate([
            'patient_user_id'       => $patientId,
            'chargeable_month'      => $month,
            'chargeable_service_id' => $this->chargeableSercviceId($chargeableServiceCode),
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
