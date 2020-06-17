<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Listeners;

use CircleLinkHealth\Core\Jobs\NotificationStatusUpdateJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Queue\InteractsWithQueue;

class LogFailedNotification implements ShouldQueue
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
    public function handle(NotificationFailed $event)
    {
        if (property_exists(get_class($event->notifiable), 'id')) {
            \Log::error("Notification sent for user with id: {$event->notifiable->id}. Data: ".json_encode($event->data));
        }

        if (method_exists($event->notification, 'failed')) {
            $event->notification->failed($event);

            return;
        }

        $this->defaultHandler($event);
    }

    private function defaultHandler(NotificationFailed $event)
    {
        $channel = $event->channel;

        NotificationStatusUpdateJob::dispatch(
            $event->notification->id,
            $channel,
            [
                'value'   => 'failed',
                'details' => $event->data['message'],
            ],
        );
    }
}
