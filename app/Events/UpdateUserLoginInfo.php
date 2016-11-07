<?php

namespace App\Events;

use App\Models\PatientSession;
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
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $event->user->last_login = Carbon::now()->toDateTimeString();
        $event->user->is_online = true;
        $event->user->save();

        //CLEAR OUT ANY REMAINING PATIENT SESSIONS ON LOGIN
        $session = PatientSession::where('user_id', '=', $event->user->id)
            ->delete();
    }
}
