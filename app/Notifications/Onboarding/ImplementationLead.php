<?php

namespace App\Notifications\Onboarding;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/*
 * Subject: Welcome to CircleLink Health!

    Hi [Implementation lead name]!

    You just launched [organization name]’s Personalized Care Management program with CircleLink Health!

    You can view your dashboard here [here is linked to dashboard].

    If you have any questions, please give us a call at [client services number]

    Welcome aboard!
    CircleLink Team

 */

class ImplementationLead extends Notification
{
    use Queueable;

    private $link;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($url)
    {
        $this->link = $url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
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
            ->subject("Welcome to CircleLink Health!")
            ->greeting("Dear $notifiable->fullName:")
            ->line(" You just launched [organization name]’s Personalized Care Management program with CircleLink Health!")
            ->line(" If you have any questions, please <a href='mailto:contact@circlelinkhealth.com'>email</a> us")
            ->action('Go To Dashboard', $this->link);
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
}
