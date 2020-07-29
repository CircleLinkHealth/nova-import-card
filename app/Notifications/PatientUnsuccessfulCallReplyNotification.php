<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioSmsMessage;

class PatientUnsuccessfulCallReplyNotification extends Notification
{
    use Queueable;

    /** @var array */
    private $channels;

    /** @var string */
    private $forwardedToNurseName;

    /**
     * Create a new notification instance.
     *
     * @param mixed $forwardedToNurseName
     *
     * @return void
     */
    public function __construct($forwardedToNurseName, array $channels = [])
    {
        $this->channels             = $channels;
        $this->forwardedToNurseName = $forwardedToNurseName;
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
            'forwarded_to_nurse' => $this->forwardedToNurseName,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('We have received your message!')
            ->line($this->getMessage());
    }

    public function toTwilio($notifiable)
    {
        return (new TwilioSmsMessage())
            ->content($this->getMessage());
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
        return array_merge(['database'], $this->channels);
    }

    private function getMessage(): string
    {
        return "Perfect! We've forwarded your message to Nurse $this->forwardedToNurseName. Thank you and have a great day :)";
    }
}
