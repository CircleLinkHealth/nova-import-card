<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Jobs\LogSuccessfulLogoutToDB;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogSuccessfulLogout
{
    /**
     * @param Logout $event
     */
    public function handle(Logout $event)
    {
        LogSuccessfulLogoutToDB::dispatch($event)->onQueue('low');
    }
}
