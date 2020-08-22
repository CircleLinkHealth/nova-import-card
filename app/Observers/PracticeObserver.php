<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Constants;
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
            ->whereDoesntHave('practices', function ($q) use ($practice) {
                $q->where('practices.id', $practice->id);
            })
            ->get()
            ->each(function ($user) use ($practice) {
                foreach ($user->roles as $role) {
                    $user->attachRoleForSite($role->id, $practice->id);
                }
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
    
    public function saved(Practice $practice) {
        if ($practice->isDirty('default_user_scope') && $practice->default_user_scope !== $practice->getOriginal('default_user_scope')) {
            User::ofPractice($practice)->ofType(Constants::PRACTICE_STAFF_ROLE_NAMES)->update([
                'scope' => $practice->default_user_scope
            ]);
        }
    }
}
