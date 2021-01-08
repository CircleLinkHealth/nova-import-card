<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Listeners;

use CircleLinkHealth\Core\Jobs\NotificationStatusUpdateJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogSentMailNotification
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
     */
    public function handle(MessageSent $event)
    {
        if ( ! isset($event->data['__laravel_notification_id'])) {
            Log::warning('could not find notification id in MessageSent event');

            return;
        }

        $this->defaultHandler($event);
    }

    private function defaultHandler(MessageSent $event)
    {
        $props = [
            'value'   => 'sent',
            'details' => now()->toDateTimeString(),
        ];

        NotificationStatusUpdateJob::dispatch(
            $event->data['__laravel_notification_id'],
            'mail',
            $props,
        );
    }
}
