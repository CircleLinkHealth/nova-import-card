<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Events;

use App\Jobs\PostLoginTasks;
use Illuminate\Auth\Events\Login;

class UpdateUserLoginInfo
{
    /**
     * Handle the event.
     *
     * @param Login $event
     */
    public function handle(Login $event)
    {
        PostLoginTasks::dispatch($event);
    }
}
