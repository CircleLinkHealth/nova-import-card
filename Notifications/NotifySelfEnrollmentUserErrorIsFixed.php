<?php

namespace CircleLinkHealth\SelfEnrollment\Notifications;

use CircleLinkHealth\Core\Exceptions\InvalidArgumentException;
use CircleLinkHealth\SelfEnrollment\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioSmsMessage;

class NotifySelfEnrollmentUserErrorIsFixed extends Notification
{
    use Queueable;

    private string $invitationLink;

    /**
     * Create a new notification instance.
     *
     * @param string $invitationLink
     */
    public function __construct(string $invitationLink)
    {
        $this->invitationLink = $invitationLink;
    }

    /**
     * @param User $notifiable
     */
    public function toTwilio(User $notifiable)
    {
        if (empty($this->invitationLink)) {
            throw new InvalidArgumentException("`invitationLink` cannot be empty. User ID {$notifiable->id}");
        }

        $subject = $this->getSubject();

        return (new TwilioSmsMessage())
            ->content($subject);
    }
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['twilio'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    private function getSubject()
    {
        return "Our apologies about the error you experienced earlier when trying to get your care coach. Our team has fixed this error. Whenever you have a chance, please visit $this->invitationLink";
    }
}
