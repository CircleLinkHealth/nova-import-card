<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Notifications\Channels;

use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\Twilio;
use NotificationChannels\Twilio\TwilioChannel;

class CustomMailChannel extends MailChannel
{
    /**
     * @var Twilio
     */
    protected $twilio;

    /**
     * TwilioChannel constructor.
     */
    public function __construct(MailFactory $mailer, Markdown $markdown)
    {
        parent::__construct($mailer, $markdown);
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            $message = $notification->toMail($notifiable);

            if ( ! $notifiable->routeNotificationFor('mail', $notification) &&
                ! $message instanceof Mailable) {
                if (property_exists($notifiable, 'id')) {
                    $msg = "could not send mail to $notifiable->id";
                } else {
                    $className = get_class($notifiable);
                    $msg       = "could not send mail to $className";
                }

                throw new \Exception($msg);
            }

            if ($message instanceof Mailable) {
                return $message->send($this->mailer);
            }

            $this->mailer->send(
                $this->buildView($message),
                array_merge($message->data(), $this->additionalMessageData($notification)),
                $this->messageBuilder($notifiable, $notification, $message)
            );
        } catch (\Exception $exception) {
            $event = new NotificationFailed($notifiable, $notification, 'mail', ['message' => $exception->getMessage()]);
            if (function_exists('event')) { // Use event helper when possible to add Lumen support
                event($event);
            } else {
                $this->events->fire($event);
            }
            //we want to throw so that NotificationSent event will not be raised.
            throw $exception;
        }
    }
}
