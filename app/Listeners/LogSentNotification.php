<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Jobs\NotificationStatusUpdateJob;
use App\Notifications\Channels\CustomTwilioChannel;
use App\SelfEnrollment\Notifications\SelfEnrollmentInviteNotification;
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
        \Log::error("Notification sent for user with id: {$event->notifiable->id}.");

        if (SelfEnrollmentInviteNotification::class === get_class($event->notification)) {
            $props = [
                'value'   => 'pending',
                'details' => now()->toDateTimeString(),
            ];

            $channel = $event->channel;
            if ('twilio' === $event->channel || CustomTwilioChannel::class === $event->channel) {
                $channel = 'twilio';
            }

            if ($event->response && 'twilio' === $channel) {
                if ($event->response->sid) {
                    $props['sid'] = $event->response->sid;
                }
                if ($event->response->accountSid) {
                    $props['accountSid'] = $event->response->accountSid;
                }
            }

            NotificationStatusUpdateJob::dispatch(
                $event->notification->id,
                $channel,
                $props,
            );
        }
    }
}
