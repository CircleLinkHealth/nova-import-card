<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Notifications\Channels;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Messages\MailMessage;
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

            $callback = function ($mailMessage) use ($message, $notifiable, $notification) {
                if ($this->isPostmark($message)) {
                    // Message-ID and X-PM-KeepID (which is added automatically in wildbit/swiftmailer-postmark/src/Postmark/Transport.php)
                    // did not work (as suggested from Postmark docs)
                    // so, solution: add this in order to match the webhook with the notification in db
                    $mailMessage->getSwiftMessage()->getHeaders()->addTextHeader('X-PM-Metadata-smtp-id', $mailMessage->getId());
                }

                $inception = $this->messageBuilder($notifiable, $notification, $message);

                return $inception($mailMessage);
            };

            $this->mailer->mailer($message->mailer ?? null)->send(
                $this->buildView($message),
                array_merge($message->data(), $this->additionalMessageData($notification)),
                $callback
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

    private function isPostmark(MailMessage $message)
    {
        return 'postmark' === ($message->mailer ?? config('mail.default'));
    }
}
