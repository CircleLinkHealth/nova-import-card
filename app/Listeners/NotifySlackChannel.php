<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\DirectMailMessage;
use App\Services\PhiMail\Events\DirectMailMessageReceived;
use App\Services\PhiMail\IncomingMessageHandler;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class NotifySlackChannel implements ShouldQueue
{
    use InteractsWithQueue;

    const ELIGIBILITY_PROCESSING_PURPOSE = 'Eligibility Processing';
    const IMPORTING_PURPOSE              = 'Importing';

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
                ? self::ELIGIBILITY_PROCESSING_PURPOSE
                : self::IMPORTING_PURPOSE;

            $greeting .= " from $practiceName, containing CCDA(s) for $purpose";
        }

        $greeting .= '.';

        $messageLink = route('direct-mail.show', [$dm->id]);

        $message = "$greeting \n Click this link to go to the message {$messageLink}.";

        if (self::ELIGIBILITY_PROCESSING_PURPOSE === $purpose
            && $runningBatch = EligibilityBatch::where('practice_id', $dm->ccdas->pluck('practice.id')->filter()->values()->first())
                ->where('status', EligibilityBatch::RUNNING)
                ->first()) {
            $message .= "\n Click this link to see the running eligibility batch ".route('eligibility.batch.show', [$runningBatch->id]);
        }

        sendSlackMessage(
            '#ccd-file-status',
            $message
        );
    }
}
