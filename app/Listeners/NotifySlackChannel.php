<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\DirectMailMessage;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifySlackChannel implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(DirectMailMessageReceived $event)
    {
        if ($event->directMailMessage->num_attachments > 0) {
            $this->notifyAdmins($event->directMailMessage);
        }
    }

    /**
     * This is to help notify us of the status of CCDs we receive.
     */
    private function notifyAdmins(
        DirectMailMessage $dm
    ) {
        if (app()->environment('local')) {
            return;
        }

        $messageLink = route('direct-mail.show', [$dm->id]);

        sendSlackMessage(
            '#ccd-file-status',
            "We received a message from EMR Direct. \n Click here to see the message {$messageLink}."
        );
    }
}
