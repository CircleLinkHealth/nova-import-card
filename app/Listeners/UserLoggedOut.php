<?php

namespace App\Listeners;

use App\PageTimer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Events\Logout;
use Carbon\Carbon;

class UserLoggedOut
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
     * @param  object  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $user = $event->user;
        if ($user) {
            $user->is_online  = false;
            $user->save();

            $activity = new PageTimer();
            $activity->duration = 0;
            $activity->billable_duration = 0;
            $activity->duration_unit = 'seconds';
            $activity->activity_type = 'logout';
            $activity->title = 'Logout';
            $activity->url_short = '/auth/logout/';
            $activity->url_full = url()->current();
            $activity->patient_id = $user->id;
            $activity->start_time = Carbon::now();
            $activity->end_time = Carbon::now();
            $activity->program_id = $user->program_id;
            $activity->save();
        }
    }
}
