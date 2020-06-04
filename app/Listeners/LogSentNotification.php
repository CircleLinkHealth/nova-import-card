<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Queue\InteractsWithQueue;

class LogSentNotification implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(NotificationSent $event)
    {
        if (property_exists(get_class($event->notifiable), 'id')) {
            \Log::info("Notification sent for user with id: {$event->notifiable->id}.");
        }

        if (method_exists($event->notification, 'sent')) {
            $event->notification->sent($event);
        }
    }
}
