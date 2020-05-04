<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendSurveyLinkToEnrollable extends Notification
{
//    **NOTE** This disabled till decide if necessary.
    use Queueable;
    private $url;

    /**
     * Create a new notification instance.
     *
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
//        @todo:  should not say "Regards AWV" & also send sms MAYBE emmit an event
        return (new MailMessage)
            ->line('You have successfully logged in to your Enrollment Survey.')
            ->line('Please use the link bellow if you wish to fill the survey some other time.')
            ->action('Enrollment Survey', url($this->url))
            ->line('Thank you for using our application!')
            ->line('Please keep in mind that the link above will expire in 2 days from now');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
