<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Policies;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PatientForcedChargeableServicePolicy
{
    use HandlesAuthorization;

    public function create(User $user, PatientForcedChargeableService $service = null)
    {
        if (is_null($service)) {
            return true;
        }

        $monthOfEffect = $service->chargeable_month ?? Carbon::now()->startOfMonth();

        $serviceIsOrWasAvailableForLocation = ChargeableLocationMonthlySummary::where('location_id', $service->patient->getPreferredContactLocation())
            ->where('chargeable_month', $monthOfEffect)
            ->exists();

        $patientAlreadyHasForcedCS = $service->patient->forcedChargeableServices()
                                                          ->where('chargeable_service_id', $service->id)
                                                          ->where('chargeable_month', $service->chargeable_month)
                                                          ->exists();

        return $user->isAdmin() && $serviceIsOrWasAvailableForLocation && ! $patientAlreadyHasForcedCS;
    }

    public function delete(User $user, PatientForcedChargeableService $service)
    {
        if ( ! $user->isAdmin()) {
            return false;
        }

        if (is_null($service->chargeable_month)) {
            return true;
        }

        if (is_null($service->patient)) {
            return true;
        }

        return ! $service->patient->monthlyBillingStatus()
            ->where('chargeable_month', $service->chargeable_month)
            ->where(function ($q) {
                $q->whereNotNull('actor_id')
                    ->orWhere('status', 'approved');
            })
            ->exists();
    }

    public function store(User $user, PatientForcedChargeableService $service)
    {
        $monthOfEffect = $service->chargeable_month ?? Carbon::now()->startOfMonth();

        $serviceIsOrWasAvailableForLocation = ChargeableLocationMonthlySummary::where('location_id', $service->patient->getPreferredContactLocation())
                                                                              ->where('chargeable_month', $monthOfEffect)
                                                                              ->exists();

        $patientAlreadyHasForcedCS = $service->patient->forcedChargeableServices()
                                                      ->where('chargeable_service_id', $service->id)
                                                      ->where('chargeable_month', $service->chargeable_month)
                                                      ->exists();

        return $user->isAdmin() && $serviceIsOrWasAvailableForLocation && ! $patientAlreadyHasForcedCS;
    }

    public function update(User $user, PatientForcedChargeableService $service)
    {
        return $user->isAdmin();
    }

    public function view(User $user, PatientForcedChargeableService $service)
    {
        return true;
    }
}
