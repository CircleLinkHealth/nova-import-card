<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Notifications\Channels;

use CircleLinkHealth\Core\Exceptions\CannotSendNotificationException;
use CircleLinkHealth\SharedModels\Entities\NotificationsExclusion;
use CircleLinkHealth\TwilioIntegration\Services\TwilioInterface;
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
     * @throws CannotSendNotificationException
     * @throws CouldNotSendNotification
     * @throws \Twilio\Exceptions\TwilioException
     * @return mixed
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            if (isset($notifiable->id) && $this->isUserBlackListed($notifiable->id)) {
                throw new CannotSendNotificationException("User[$notifiable->id] is in sms exclusions list. Will not send sms.");
            }

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
            $event = new NotificationFailed(
                $notifiable,
                $notification,
                'twilio',
                ['message' => $exception->getMessage()]
            );

            $this->events->dispatch($event);

            if ($this->twilio->config->isIgnoredErrorCode($exception->getCode())) {
                return;
            }

            throw $exception;
        }
    }

    private function isUserBlackListed($userId)
    {
        return NotificationsExclusion::isSmsBlackListed($userId);
    }
}
