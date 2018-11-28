<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Auth\Events\Login;

class UpdateUserLoginInfo
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login $event
     *
     * @return void
     */
    public function handle(Login $event)
    {
        $event->user->last_login = Carbon::now()->toDateTimeString();
        $event->user->is_online  = true;

        if ($event->user->isAdmin() && $event->user->authy_id && ! $event->user->is_authy_enabled) {
            $event->user->is_authy_enabled = true;
        }

        $event->user->save();
    }
}
