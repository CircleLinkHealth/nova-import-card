<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;

class PracticeObserver
{
    /**
     * Listen to the Practice created event.
     */
    public function created(Practice $practice)
    {
        User::withTrashed()
            ->where('saas_account_id', $practice->saas_account_id)
            ->where('auto_attach_programs', true)
            ->with('roles')
            ->has('roles')
            ->whereDoesntHave('practices', function ($q) use ($practice) {
                $q->where('practices.id', $practice->id);
            })
            ->get()
            ->map(function ($user) use ($practice) {
                $user->attachRoleForSite($user->roles->first()->id, $practice->id);
            });
    }

    /**
     * Listen to the Practice creating event.
     */
    public function creating(Practice $practice)
    {
        if ( ! $practice->saas_account_id && ! auth()->guest()) {
            $practice->saas_account_id = auth()->user()->saas_account_id;
        }
    }
}
