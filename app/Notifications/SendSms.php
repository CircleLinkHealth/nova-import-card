<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioSmsMessage;

class SendSms extends Notification
{
    use Queueable;

    /**
     * @var string
     */
    private $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'notifiable' => $notifiable,
            'message'    => $this->message,
        ];
    }

    public function toTwilio($notifiable)
    {
        return (new TwilioSmsMessage())
            ->content($this->message);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['twilio'];
    }
}
