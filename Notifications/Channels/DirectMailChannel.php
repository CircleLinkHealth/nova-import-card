<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Notifications\Channels;

use CircleLinkHealth\Core\Contracts\DirectMail;
use CircleLinkHealth\Core\Contracts\DirectMailableNotification;
use CircleLinkHealth\SharedModels\Entities\DirectMailMessage;
use App\Services\PhiMail\SendResult;
use CircleLinkHealth\Core\DTO\SimpleNotification;
use CircleLinkHealth\Core\Exceptions\InvalidTypeException;

class DirectMailChannel
{
    protected $dm;

    public function __construct(DirectMail $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Send the given notification.
     *
     * @param mixed                                  $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws InvalidTypeException
     */
    public function send($notifiable, DirectMailableNotification $notification)
    {
        if ($notifiable->emr_direct_address) {
            $message = $notification->toDirectMail($notifiable);

            $this->throwExceptionIfWrongType($message, $notification);

            $sentMessage = $this->dm->send(
                $notifiable->emr_direct_address,
                $message->getFilePath(),
                $message->getFileName(),
                $message->getCcdaAttachmentPath(),
                $message->getPatient(),
                $message->getBody(),
                $message->getSubject()
            );

            if (array_key_exists(0, $sentMessage) && is_a($sentMessage[0], SendResult::class)) {
                $numAttachments = 0;

                if ($message->getFilePath()) {
                    ++$numAttachments;
                }
                if ($message->getCcdaAttachmentPath()) {
                    ++$numAttachments;
                }
                /** @var SendResult $msgObj */
                $msgObj = $sentMessage[0];
                $dm     = DirectMailMessage::create(
                    [
                        'message_id'      => $msgObj->messageId,
                        'from'            => config('services.emr-direct.user'),
                        'to'              => $msgObj->recipient,
                        'body'            => $notification->directMailBody($notifiable),
                        'subject'         => $notification->directMailSubject($notifiable),
                        'num_attachments' => $numAttachments,
                        'status'          => $msgObj->succeeded
                            ? DirectMailMessage::STATUS_SUCCESS
                            : DirectMailMessage::STATUS_FAIL,
                        'direction'  => DirectMailMessage::DIRECTION_SENT,
                        'error_text' => $msgObj->errorText,
                    ]
                );
            }
        }
    }

    /**
     * Throws an exception if the message is not of type SimpleNotification.
     *
     * @param $message
     * @param $notification
     *
     * @throws InvalidTypeException
     */
    private function throwExceptionIfWrongType($message, $notification)
    {
        if ( ! is_a($message, SimpleNotification::class)) {
            throw new InvalidTypeException('Object of invalid type returned from `'.get_class($notification).'@toDirectMail`. At '.__FILE__.':'.__LINE__);
        }
    }
}
