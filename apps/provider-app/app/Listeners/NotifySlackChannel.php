<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use CircleLinkHealth\Core\Services\PhiMail\Events\DirectMailMessageReceived;
use CircleLinkHealth\Core\Services\PhiMail\IncomingMessageHandler;
use CircleLinkHealth\SharedModels\Entities\EligibilityBatch;
use CircleLinkHealth\SharedModels\Entities\DirectMailMessage;
use Illuminate\Support\Str;

class NotifySlackChannel
{
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
        \Log::debug('Running NotifySlackChannel');
        \Log::debug('isProductionEnv='.isProductionEnv());

        if ( ! isProductionEnv()) {
            return;
        }

        $dm->loadMissing('ccdas.practice');

        $greeting = 'DM Received';

        $purpose = null;

        if ($practiceName = $dm->ccdas->pluck('practice.display_name')->filter()->values()->first()) {
            $purpose = Str::contains(strtolower($dm->body), strtolower(IncomingMessageHandler::KEYWORD_TO_PROCESS_FOR_ELIGIBILITY))
                ? self::ELIGIBILITY_PROCESSING_PURPOSE
                : self::IMPORTING_PURPOSE;

            $greeting .= " from $practiceName, containing CCDA(s) for $purpose";
        }

        $greeting .= '.';

        $messageLink = route('direct-mail.show', [$dm->id]);

        $message = "$greeting \n Click <{$messageLink}|here> to view message.";

        if (self::ELIGIBILITY_PROCESSING_PURPOSE === $purpose
            && $runningBatch = EligibilityBatch::where('practice_id', $dm->ccdas->pluck('practice_id')->filter()->values()->first())
                ->where('status', EligibilityBatch::RUNNING)
                ->first()) {
            $batchLink = route('eligibility.batch.show', [$runningBatch->id]);
            $message .= "\n Click <{$batchLink}|here> to see the running eligibility batch.";
        }

        sendSlackMessage(
            '#ccd-file-status',
            $message
        );
    }
}
