<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications\Channels;

use App\Contracts\DirectMail;
use CircleLinkHealth\Core\Exceptions\InvalidTypeException;
use App\ValueObjects\SimpleNotification;
use Illuminate\Notifications\Notification;

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
    public function send($notifiable, Notification $notification)
    {
        if ($notifiable->emr_direct_address) {
            $message = $notification->toDirectMail($notifiable);

            $this->throwExceptionIfWrongType($message, $notification);

            $this->dm->send(
                $notifiable->emr_direct_address,
                $message->getFilePath(),
                $message->getFileName(),
                $message->getCcdaAttachmentPath(),
                $message->getPatient(),
                $message->getBody(),
                $message->getSubject()
            );
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
            throw new InvalidTypeException(
                'Object of invalid type returned from `'.get_class(
                    $notification
                ).'@toDirectMail`. At '.__FILE__.':'.__LINE__
            );
        }
    }
}
