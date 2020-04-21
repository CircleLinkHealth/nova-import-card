<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications\Onboarding;

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/*
 * Subject: Welcome to CircleLink Health!

    Hi [Implementation lead name]!

    You just launched [organization name]’s Personalized Care Management program with CircleLink Health!

    You can view your dashboard here [here is linked to dashboard].

    If you have any questions, please give us a call at [client services number]

    Welcome aboard!
    CircleLink Team

 */

class ImplementationLeadWelcome extends Notification
{
    use Queueable;

    private $practice;

    /**
     * Create a new notification instance.
     */
    public function __construct(Practice $practice)
    {
        $this->practice = $practice;
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
            ->subject('Welcome to '.$notifiable->saasAccountName().'!')
            ->greeting("Dear {$notifiable->getFullName()}:")
            ->line("{$this->practice->formatted_name}’s Personalized Care Management program with CircleLink Health just launched!")
            ->line('Please reset your password with below button.')
            ->action('Reset Password', url('auth/password/reset'));
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
        return ['mail'];
    }
}
