<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Jobs\LogSuccessfulLoginToDB;
use App\LoginLogout;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogin implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * @param Login $event
     */
    public function handle(Login $event)
    {
        LogSuccessfulLoginToDB::dispatch($event)->onQueue('low');
    }
}
