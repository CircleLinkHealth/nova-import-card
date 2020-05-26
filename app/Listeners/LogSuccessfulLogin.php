<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Jobs\LogSuccessfulLoginToDB;
use Illuminate\Auth\Events\Login;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogin
{
    use InteractsWithQueue;

    public function handle(Login $event)
    {
        LogSuccessfulLoginToDB::dispatch($event->user)->onQueue('low');
    }
}
