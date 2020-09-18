<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use Carbon\Carbon;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserLoggedOut implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param object $event
     */
    public function handle(Logout $event)
    {
        session()->put('last_login', null);

        $user = $event->user;
        if ($user) {
            //not really needed here, but is saves the trip to redis when trying to destroy a non-existing session
            $user->last_session_id = null;

            $user->is_online = false;
            $user->save();

            $activity                    = new PageTimer();
            $activity->duration          = 0;
            $activity->billable_duration = 0;
            $activity->duration_unit     = 'seconds';
            $activity->activity_type     = 'logout';
            $activity->title             = 'Logout';
            $activity->url_short         = '/auth/logout/';
            $activity->url_full          = url()->current();
            $activity->patient_id        = null;
            $activity->provider_id       = $user->id;
            $activity->start_time        = Carbon::now();
            $activity->end_time          = Carbon::now();
            $activity->program_id        = $user->program_id;
            $activity->save();
        }
    }
}
