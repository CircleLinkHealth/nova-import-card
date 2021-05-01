<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Notifications\Channels;

use CircleLinkHealth\Core\Exceptions\CannotSendNotificationException;
use CircleLinkHealth\Core\Notifications\DuplicateNotificationChecker;
use CircleLinkHealth\SharedModels\Entities\NotificationsExclusion;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;

class CustomMailChannel extends MailChannel
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     *
     * @throws \Exception
     * @return mixed
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            if (isset($notifiable->id) && $this->isUserBlackListed($notifiable->id)) {
                throw new CannotSendNotificationException("User[$notifiable->id] is in mail exclusions list. Will not send mail.");
            }

            if (DuplicateNotificationChecker::hasAlreadySentNotification($notifiable, $notification, 'mail')) {
                throw new CannotSendNotificationException('Notification has already been sent. Please check DB.');
            }

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

            $this->mailer->mailer($message->mailer ?? null)->send(
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
        }
    }

    private function isUserBlackListed($userId)
    {
        return NotificationsExclusion::isMailBlackListed($userId);
    }
}
