<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;

class UpdateUserSessionInfo
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
    public function handle(Authenticated $event)
    {
        $shouldUpdate = false;

        if (empty($event->user->last_session_id)) {
            $shouldUpdate = true;
        } elseif (session()->getId() != $event->user->last_session_id) {
            session()->getHandler()->destroy($event->user->last_session_id);
            $shouldUpdate = true;
        }

        if ($shouldUpdate) {
            $event->user->last_session_id = session()->getId();
            $event->user->save();
        }
    }
}
