<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Notifications\Channels;

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

            return parent::send($notifiable, $notification);
        } catch (\Exception $exception) {
            $event = new NotificationFailed($notifiable, $notification, 'mail', ['message' => $exception->getMessage()]);

            if (function_exists('event')) { // Use event helper when possible to add Lumen support
                event($event);
            } else {
                $this->events->fire($event);
            }
        }
    }
}
