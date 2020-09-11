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
    
    private array $channels;
    
    private ?string $forwardedToNurseName;
    
    private ?string $practiceName;

    /**
     * Create a new notification instance.
     *
     * @param mixed $forwardedToNurseName
     * @param mixed $practiceName
     *
     * @return void
     */
    public function __construct(?string $forwardedToNurseName, ?string $practiceName, array $channels = [])
    {
        $this->channels             = $channels;
        $this->forwardedToNurseName = $forwardedToNurseName;
        $this->practiceName         = $practiceName;
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
        $mailMessage           = new MailMessage();
        $mailMessage->viewData = ['excludeLogo' => true, 'practiceName' => $this->practiceName];
        $fromAddress           = config('mail.from-with-inbound.address') ?? config('mail.from.address');

        return $mailMessage
            ->from($fromAddress, config('mail.from-with-inbound.name'))
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
        if (isEmpty($this->forwardedToNurseName)) {
            return "Perfect! We've forwarded your message to your care coach. Thank you and have a great day :)";
        }
        return "Perfect! We've forwarded your message to Nurse $this->forwardedToNurseName. Thank you and have a great day :)";
    }
}
