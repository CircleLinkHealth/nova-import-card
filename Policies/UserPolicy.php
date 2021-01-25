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

    public function attachChargeableService()
    {
        return false;
    }

    public function attachAnyChargeableService(){
        return true;
    }

    public function detachChargeableService(User $user, User $patient, ChargeableService $service)
    {
        return $user->monthlyBillingStatus()
                    ->where('chargeable_month', $service->forcedDetails->chargeable_month)
                    ->where(fn($q) => $q->whereNull('actor_id')->orWhere('status', 'approved'))
                    ->exists();
    }
}