<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Policies;


use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PatientForcedChargeableServicePolicy
{
    use HandlesAuthorization;

    public function create(User $user, PatientForcedChargeableService $service = null)
    {
        if (is_null($service)){
            return true;
        }
        return $user->isAdmin() && ! $service->patient->forcedChargeableServices()
                ->where('chargeable_service_id', $service->id)
                ->where('chargeable_month', $service->chargeable_month)
                ->exists();
    }

    public function store(User $user, PatientForcedChargeableService $service)
    {
        return $user->isAdmin() && ! $service->patient->forcedChargeableServices()
                                                      ->where('chargeable_service_id', $service->id)
                                                      ->where('chargeable_month', $service->chargeable_month)
                                                      ->exists();
    }

    public function delete(User $user, PatientForcedChargeableService $service)
    {
        if (! $user->isAdmin()){
            return false;
        }

        if (is_null($service->chargeable_month)) {
            return true;
        }

        if (is_null($service->patient)){
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

    public function update(User $user, PatientForcedChargeableService $service)
    {
        return false;
    }

    public function view(User $user, PatientForcedChargeableService $service)
    {
        return true;
    }
}