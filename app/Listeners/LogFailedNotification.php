<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Jobs\NotificationStatusUpdateJob;
use App\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;
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
        \Log::error("Notification failed for user with id: {$event->notifiable->id}. Data: ".json_encode($event->data));

        if (SelfEnrollmentInviteNotification::class === get_class($event->notification)) {
            NotificationStatusUpdateJob::dispatch(
                $event->notification->id,
                $event->channel,
                [
                    'value'   => 'failed',
                    'details' => $event->data['message'],
                ],
            );
        }
    }
}
