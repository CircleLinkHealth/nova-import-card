<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Policies;


use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function view(){
        return true;
    }

    public function create(){
        return true;
    }

    public function update(){
        return true;
    }

    public function delete()
    {
        return true;
    }

    public function attachChargeableService(User $user, User $patient, ChargeableService $service)
    {
        if ($patient->forcedChargeableServices()->where('chargeable_service_id', $service->id)->where('chargeable_month', optional($service->forcedDetails)->chargeable_month)->exists()){
            return false;
        }
        return $this->attachAnyChargeableService();
    }

    public function attachAnyChargeableService(){
        return true;
    }

    public function detachChargeableService(User $user, User $patient, ChargeableService $service)
    {
        return $user->monthlyBillingStatus()
                    ->when(! is_null($service->forcedDetals->chargeable_month),
                        fn($q) => $q->where('chargeable_month', $service->forcedDetails->chargeable_month)
                    )
                    ->where(fn($q) => $q->whereNull('actor_id')->orWhere('status', 'approved'))
                    ->exists();
    }
}