<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\PhiMail;

use App\DirectMailMessage;
use App\Services\PhiMail\Incoming\Factory as IncomingMessageHandlerFactory;

/**
 * Handle an incoming message from EMR Direct Mail API.
 *
 * Class IncomingMessageHandler
 */
class IncomingMessageHandler
{
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
                'direction'       => DirectMailMessage::DIRECTION_RECEIVED,
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
