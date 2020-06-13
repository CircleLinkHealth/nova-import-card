<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Jobs\NotificationStatusUpdateJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Channels\MailChannel;
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

            return;
        }

        $this->defaultHandler($event);
    }

    private function defaultHandler(NotificationSent $event)
    {
        $props = [
            'value'   => 'pending',
            'details' => now()->toDateTimeString(),
        ];

        $channel = $event->channel;

        // {@link MailChannel} raises MessageSent event which is handled in {@link LogSentMailNotification}
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
