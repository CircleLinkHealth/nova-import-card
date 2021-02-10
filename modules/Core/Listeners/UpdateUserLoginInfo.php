<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Listeners;

use CircleLinkHealth\Core\Jobs\PostLoginTasks;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Core\Jobs\LogSuccessfulLoginToDB;
use Illuminate\Auth\Events\Login;

class UpdateUserLoginInfo
{
    /**
     * Handle the event.
     */
    public function handle(Login $event)
    {
        //need to do this here because we don't want to queue,
        //it must happen before the response is sent to the client
        if ( ! empty($event->user->last_login)) {
            session()->put('last_login', $event->user->last_login);
        }
        LogSuccessfulLoginToDB::dispatch($event->user)->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
        PostLoginTasks::dispatch($event->user)->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
    }
}