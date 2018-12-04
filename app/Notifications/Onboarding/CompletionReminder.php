<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications\Onboarding;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompletionReminder extends Notification
{
    /*
     * Subject: Let’s Finish Your CircleLink Health Profile!

    Hi [User name],

    We’re excited to start providing best-in-class chronic care management at [organization name] but we need you to finish your profile by clicking here. [link to wherever user/lead left off]

    Thanks and welcome aboard!
    CircleLink Team
     */

    use Queueable;

    private $link;

    /**
     * Create a new notification instance.
     *
     * @param mixed $url
     */
    public function __construct(
        $url
    ) {
        $this->link = $url;
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
        $program_name = $notifiable->primaryPractice->display_name;

        return (new MailMessage())
            ->subject('Let’s Finish Your'.$notifiable->saasAccountName().' Profile!')
            ->greeting("Hi {$notifiable->getFullName()}:")
            ->line("We’re excited to start providing best-in-class chronic care management at ${program_name} but we need you to finish your profile!")
            ->line('Thanks and welcome aboard!')
            ->action('Complete Profile', $this->link);
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
