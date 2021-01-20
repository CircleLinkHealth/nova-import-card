<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Listeners;

use CircleLinkHealth\Core\Jobs\NotificationStatusUpdateJob;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Queue\InteractsWithQueue;

class LogMailSmtpId
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(MessageSending $event)
    {
        if ( ! isset($event->data['__laravel_notification_id'])) {
            return;
        }

        $props = [
            'value'   => 'sending',
            'details' => now()->toDateTimeString(),
        ];

        if ($event->message) {
            $props['smtp_id'] = $event->message->getId();
        }

        NotificationStatusUpdateJob::dispatch(
            $event->data['__laravel_notification_id'],
            'mail',
            $props,
        );
    }
}