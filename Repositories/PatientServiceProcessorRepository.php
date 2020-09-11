<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientServicesQuery;
use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository as Repository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\ChargeableService as ChargeableServiceModel;
use CircleLinkHealth\Customer\Entities\Patient as PatientModel;

class PatientServiceProcessorRepository implements Repository
{
    use ApprovablePatientServicesQuery;

    public function fulfill(int $patientId, string $chargeableServiceCode, Carbon $month): ChargeablePatientMonthlySummary
    {
        return ChargeablePatientMonthlySummary::updateOrCreate([
            'patient_user_id'       => $patientId,
            'chargeable_month'      => $month,
            'chargeable_service_id' => ChargeableService::getChargeableServiceIdUsingCode($chargeableServiceCode),
        ], [
            'is_fulfilled' => true,
        ]);
    }

    public function getChargeablePatientSummaries(int $patientId, Carbon $month)
    {
        //todo: use view
        return ChargeablePatientMonthlySummary::with(['chargeableService' => function ($cs) {
            $cs->select(['id', 'display_name']);
        }])
            ->where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->get();
    }

    public function getChargeablePatientSummary(int $patientId, string $chargeableServiceCode, Carbon $month): ?ChargeablePatientMonthlySummaryView
    {
        return ChargeablePatientMonthlySummaryView::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->first();
    }

    public function isAttached(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return ChargeablePatientMonthlySummaryView::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->exists();
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
        return ChargeablePatientMonthlySummaryView::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->where('is_fulfilled', true)
            ->exists();
    }

    public function requiresPatientConsent(int $patientId, string $chargeableServiceCode, Carbon $month): bool
    {
        return ChargeablePatientMonthlySummaryView::where('patient_user_id', $patientId)
            ->where('chargeable_month', $month)
            ->where('chargeable_service_code', $chargeableServiceCode)
            ->where('requires_patient_consent', true)
            ->exists();
    }

    public function setPatientConsented(int $patientId, string $chargeableServiceCode, Carbon $month) : ChargeablePatientMonthlySummary
    {
        return ChargeablePatientMonthlySummary::updateOrCreate([
            'patient_user_id'       => $patientId,
            'chargeable_month'      => $month,
            'chargeable_service_id' => ChargeableService::getChargeableServiceIdUsingCode($chargeableServiceCode),
        ], [
            'requires_patient_consent' => false,
        ]);
    }

    public function store(int $patientId, string $chargeableServiceCode, Carbon $month, bool $requiresPatientConsent = false): ChargeablePatientMonthlySummary
    {
        return ChargeablePatientMonthlySummary::updateOrCreate(
            [
                'patient_user_id'       => $patientId,
                'chargeable_month'      => $month,
                'chargeable_service_id' => ChargeableService::getChargeableServiceIdUsingCode($chargeableServiceCode),
            ],
            [
                'requires_patient_consent' => $requiresPatientConsent,
            ]
        );
    }
}
