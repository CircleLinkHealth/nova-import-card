<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\QueryException;

class UserObserver
{
    /**
     * Listen to the User creating event.
     */
    public function creating(User $user)
    {
    }

    /**
     * Listen to the User deleting event.
     */
    public function deleting(User $user)
    {
    }

    /**
     * Listen to the User saved event.
     */
    public function saved(User $user)
    {

    }

    /**
     * Listen to the User saving event.
     */
    public function saving(User $user)
    {
        if ( ! $user->saas_account_id) {
            $practice = $user->practices->first();

            if ($practice) {
                $user->saas_account_id = $practice->saas_account_id;
            } elseif (auth()->check()) {
                $user->saas_account_id = auth()->user()->saas_account_id;
            }
        }
    }
}
