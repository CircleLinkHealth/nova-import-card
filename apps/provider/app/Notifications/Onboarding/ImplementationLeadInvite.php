<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications\Onboarding;

use CircleLinkHealth\Customer\Entities\Invite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImplementationLeadInvite extends Notification
{
    use Queueable;
    private $invite;

    /**
     * Create a new notification instance.
     *
     * @param \CircleLinkHealth\Customer\Database\Migrations\Invite $invite
     */
    public function __construct(Invite $invite)
    {
        $this->invite = $invite;
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
            ->subject($this->invite->subject)
            ->greeting('Hi there,')
            ->line($this->invite->message)
            ->action('Create Implementation', route('get.onboarding.create.program.lead.user', [
                'code' => $this->invite->code,
            ]))
            ->line('If you have any questions, please <a href="mailto:contact@circlelinkhealth.com">email us</a>.');
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
