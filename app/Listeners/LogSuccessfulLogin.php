<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Jobs\LogSuccessfulLoginToDB;
use App\LoginLogout;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin
{
    /**
     * @param Login $event
     */
    public function handle(Login $event)
    {
        LogSuccessfulLoginToDB::dispatch($event)->onQueue('low');
    }
}
