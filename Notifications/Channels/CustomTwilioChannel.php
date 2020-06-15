<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Notifications\Channels;

use CircleLinkHealth\Core\TwilioInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\Exceptions\CouldNotSendNotification;
use NotificationChannels\Twilio\Twilio;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioMessage;
use NotificationChannels\Twilio\TwilioSmsMessage;

class CustomTwilioChannel extends TwilioChannel
{
    /**
     * @var Twilio
     */
    protected $twilio;

    /**
     * TwilioChannel constructor.
     */
    public function __construct(TwilioInterface $twilio, Dispatcher $events)
    {
        $this->twilio = $twilio;
        $this->events = $events;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     *
     * @throws CouldNotSendNotification
     *
     * @return mixed
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            $to        = $this->getTo($notifiable);
            $message   = $notification->toTwilio($notifiable);
            $useSender = $this->canReceiveAlphanumericSender($notifiable);

            if (is_string($message)) {
                $message = new TwilioSmsMessage($message);
            }

            if ( ! $message instanceof TwilioMessage) {
                throw CouldNotSendNotification::invalidMessageObject($message);
            }

            $message->statusCallback       = route('twilio.sms.status');
            $message->statusCallbackMethod = 'POST';

            return $this->twilio->sendMessage($message, $to, $useSender);
        } catch (\Exception $exception) {
            $event = new NotificationFailed($notifiable, $notification, 'twilio', ['message' => $exception->getMessage()]);
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
