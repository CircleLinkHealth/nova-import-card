<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail;

use App\DirectMailMessage;
use App\Jobs\DecorateUPG0506CcdaWithPdfData;
use App\Jobs\ImportCcda;
use App\Services\PhiMail\Incoming\Factory as IncomingMessageHandlerFactory;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Jobs\CheckCcdaEnrollmentEligibility;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Str;

/**
 * Handle an incoming message from EMR Direct Mail API.
 *
 * Class IncomingMessageHandler
 */
class IncomingMessageHandler
{
    const KEYWORD_TO_PROCESS_FOR_ELIGIBILITY = '#eligibility';

    /**
     * Creates a new Direct Message.
     *
     * @return DirectMailMessage
     */
    public function createNewDirectMessage(CheckResult $message)
    {
        return DirectMailMessage::create(
            [
                'direction'       => DirectMailMessage::DIRECTION_RECEIVED,
                'message_id'      => $message->messageId,
                'from'            => $message->sender,
                'to'              => $message->recipient,
                'body'            => $message->info,
                'num_attachments' => $message->numAttachments,
                'status'          => $message->statusCode ?? DirectMailMessage::STATUS_SUCCESS,
            ]
        );
    }

    /**
     * @return mixed
     */
    public function handleMessageAttachment(DirectMailMessage $dm, ShowResult $showRes)
    {
        return IncomingMessageHandlerFactory::create($dm, $showRes)->handle();
    }

    public function processCcdas(DirectMailMessage $dm)
    {
        $dm->loadMissing('ccdas.practice');

        $dm->ccdas->each(function (Ccda $ccda) use ($dm) {
            if ($ccda->practice instanceof Practice
                && Str::contains(strtolower($dm->body), strtolower(self::KEYWORD_TO_PROCESS_FOR_ELIGIBILITY))
            ) {
                $batch = EligibilityBatch::runningBatch($ccda->practice);

                $ccda->status = Ccda::DETERMINE_ENROLLEMENT_ELIGIBILITY;
                $ccda->batch_id = $batch->id;
                $ccda->save();

                CheckCcdaEnrollmentEligibility::dispatch($ccda, $ccda->practice, $batch);

                return;
            }

            ImportCcda::withChain(
                [
                    new DecorateUPG0506CcdaWithPdfData($ccda),
                ]
            )->dispatch($ccda)->onQueue('low');
        });
    }

    /**
     * Store the subject of the message.
     *
     * @param $dm
     */
    public function storeMessageSubject(&$dm, ShowResult $showRes)
    {
        // Headers are set by the sender and may include Subject, Date, additional addresses to which the message was sent, etc.
        // Do NOT use the To: header to determine the address to which this message should be delivered internally; use $message->recipient instead.
        foreach ($showRes->headers as $header) {
            if (false !== ($pos = strpos($header, 'Subject:'))) {
                $dm->subject = trim(substr($header, $pos + 8));
                $dm->save();
            }
        }
    }
}
