<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

class LogScheduledTask
{
    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle($event)
    {
        \Log::info($message = get_class($event).': '.$event->task->command);
        if (extension_loaded('newrelic')) {
            newrelic_add_custom_parameter('ScheduledTask', $message);
        }
    }
}
