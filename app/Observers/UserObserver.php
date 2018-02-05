<?php

namespace App\Observers;

use App\Practice;
use App\User;
use Illuminate\Database\QueryException;

class UserObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  User $user
     *
     * @return void
     */
    public function created(User $user)
    {
        //
    }

    /**
     * Listen to the User creating event.
     *
     * @param  User $user
     *
     * @return void
     */
    public function creating(User $user)
    {
        //
    }

    /**
     * Listen to the User deleting event.
     *
     * @param  User $user
     *
     * @return void
     */
    public function deleting(User $user)
    {
        //
    }

    /**
     * Listen to the User saved event.
     *
     * @param  User $user
     *
     * @return void
     */
    public function saved(User $user)
    {
        if ($user->auto_attach_programs) {
            $practiceIds = $user->saasAccount
                ->practices
                ->map(function ($practice) use ($user) {
                    try {
                        $user->attachRoleForSite($user->practiceOrGlobalRole()->id, $practice->id);

                        return $practice->id;
                    } catch (\Exception $e) {
                        //check if this is a mysql exception for unique key constraint
                        if ($e instanceof QueryException) {
                            $errorCode = $e->errorInfo[1];
                            if ($errorCode == 1062) {
                                return false;
                            }
                        }
                    }
                });
        }
    }
}
