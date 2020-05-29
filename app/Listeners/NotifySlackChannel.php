<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\DirectMailMessage;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use App\Services\PhiMail\IncomingMessageHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

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
        $this->notifyAdmins($event->directMailMessage);
    }

    /**
     * This is to help notify us of the status of CCDs we receive.
     */
    private function notifyAdmins(
        DirectMailMessage $dm
    ) {
        if ( ! app()->environment('production')) {
            return;
        }

        $dm->loadMissing('ccdas.practice');

        $greeting = 'DM Received';

        if ($practiceName = $dm->ccdas->pluck('practice.display_name')->filter()->values()->first()) {
            $purpose = Str::contains(strtolower($dm->body), strtolower(IncomingMessageHandler::KEYWORD_TO_PROCESS_FOR_ELIGIBILITY))
                ? 'Eligibility Processing'
                : 'Importing';

            $greeting .= " from $practiceName, containing CCDA(s) for $purpose";
        }

        $greeting .= '.';

        $messageLink = route('direct-mail.show', [$dm->id]);

        sendSlackMessage(
            '#ccd-file-status',
            "$greeting \n Click this link to go to the message {$messageLink}."
        );
    }
}
