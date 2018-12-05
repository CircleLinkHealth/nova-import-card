<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Auth\Events\Login;

class UpdateUserLoginInfo
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param Login $event
     */
    public function handle(Login $event)
    {
        $event->user->last_login = Carbon::now()->toDateTimeString();
        $event->user->is_online  = true;

        $authyUser = optional($event->user->authyUser);

        if ($event->user->isAdmin() && (bool) config('auth.two_fa_enabled') && $authyUser->authy_id && ! $authyUser->is_authy_enabled) {
            $authyUser->is_authy_enabled = true;
            $authyUser->save();
        }

        $event->user->save();
    }
}
