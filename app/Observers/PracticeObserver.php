<?php

namespace App\Observers;

use App\Practice;
use App\User;

class PracticeObserver
{
    /**
     * Listen to the Practice creating event.
     *
     * @param Practice $practice
     *
     * @return void
     */
    public function creating(Practice $practice)
    {
        if (! $practice->saas_account_id && !auth()->guest()) {
            $practice->saas_account_id = auth()->user()->saas_account_id;
        }
    }

    /**
     * Listen to the Practice created event.
     *
     * @param Practice $practice
     *
     * @return void
     */
    public function created(Practice $practice)
    {
        $updated = User::withTrashed()
                       ->where('saas_account_id', $practice->saas_account_id)
                       ->where('auto_attach_programs', true)
                       ->with('roles')
                       ->has('roles')
                       ->whereDoesntHave('practices', function ($q) use ($practice) {
                           $q->whereId($practice->id);
                       })
                       ->get()
                       ->map(function ($user) use ($practice) {
                           $user->attachRoleForSite($user->roles->first()->id, $practice->id);
                       });
    }
}
