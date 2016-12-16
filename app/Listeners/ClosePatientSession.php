<?php

namespace App\Listeners;

use App\Models\PatientSession;
use Illuminate\Auth\Events\Logout;

class ClosePatientSession
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
     * @param  Logout $event
     *
     * @return void
     */
    public function handle(Logout $event)
    {
        //CLEAR OUT ANY REMAINING PATIENT SESSIONS ON LOGOUT
        $session = PatientSession::where('user_id', '=', $event->user->id)
            ->delete();
    }
}
