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
        if ($user->auto_attach_programs) {
            $practiceIds = $user->saasAccount
                ->practices
                ->map(function ($practice) use ($user) {
                    try {
                        $role = $user->practiceOrGlobalRole();

                        if ( ! $role) {
                            return false;
                        }

                        $user->attachRoleForSite($role->id, $practice->id);

                        return $practice->id;
                    } catch (\Exception $e) {
                        //check if this is a mysql exception for unique key constraint
                        if ($e instanceof QueryException) {
                            //                    @todo:heroku query to see if it exists, then attach

                            $errorCode = $e->errorInfo[1];
                            if (1062 == $errorCode) {
                                return false;
                            }
                        }

                        throw $e;
                    }
                });
        }
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
