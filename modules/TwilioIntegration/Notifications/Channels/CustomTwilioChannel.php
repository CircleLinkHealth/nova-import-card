<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Notifications\Channels;

use CircleLinkHealth\Core\Exceptions\CannotSendNotificationException;
use CircleLinkHealth\Core\Notifications\DuplicateNotificationChecker;
use CircleLinkHealth\SharedModels\Entities\NotificationsExclusion;
use CircleLinkHealth\TwilioIntegration\Services\TwilioInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
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

    public function canReceiveSms(?string $to): bool
    {
        $resp = $this->twilio->lookup($to);
        if ( ! empty($resp->errorDetails)) {
            Log::error("CustomTwilioChannel::canReceiveSms => $resp->errorDetails[$resp->errorCode]");

            // number could still be a mobile
            return true;
        }

        if (is_null($resp->isMobile)) {
            return true;
        }

        return $resp->isMobile;
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

            if (DuplicateNotificationChecker::hasAlreadySentNotification($notifiable, $notification, 'twilio')) {
                throw new CannotSendNotificationException('Notification has already be sent. Please check DB.');
            }

            $to = $this->getTo($notifiable);
            if (empty($to) || ! $this->canReceiveSms($to)) {
                throw new CannotSendNotificationException("Cannot send SMS to $to. Phone number[$to] is not mobile.");
            }

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
