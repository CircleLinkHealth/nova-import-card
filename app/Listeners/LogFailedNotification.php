<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationFailed;

class LogFailedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param object $event
     */
    public function handle(NotificationFailed $event)
    {
        \Log::error("Notification failed for user with id: {$event->notifiable->id}. Data: $event->data");
    }
}
