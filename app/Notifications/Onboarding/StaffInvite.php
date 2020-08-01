<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications\Onboarding;

use CircleLinkHealth\Customer\Entities\Invite;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/*
 * BODY:

    Subject: [Implementation lead name] Invited You to the Personalized Care Management Team with CircleLink!

    Dear [user name],

    [Implementation lead name] at [organization name] just invited you the Personalized Care Management team!

    Please click here to confirm and create a password. [here is linked to only pg 1 of onboarding pages without Provider checkbox, and with pre-filled except password]

    Welcome aboard!
    CircleLink Team

 */

class StaffInvite extends Notification
{
    use Queueable;

    private $code;
    private $practice;
    private $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        User $implementationLead,
        Practice $practice
    ) {
        $this->practice = $practice;
        $this->sender   = $implementationLead;

        $this->code = generateRandomString(40);
    }

    public function logInvite($notifiable)
    {
        $arr = $this->toArray($notifiable);

        return Invite::create([
            'inviter_id' => $this->sender->id,
            'role_id'    => $notifiable->roles()->first()->id,
            'email'      => $notifiable->email,
            'subject'    => $arr['subject'],
            'message'    => $arr['greeting'].PHP_EOL.$arr['line'].PHP_EOL.$arr['action_text'].PHP_EOL,
            'code'       => $this->code,
        ]);
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
        $invitor_name = $this->sender->getFullName();
        $program_name = $this->practice->formatted_name;

        return [
            'subject'     => "${invitor_name} Invited You to the Personalized Care Management Team with CircleLink!",
            'greeting'    => "Dear {$notifiable->getFullName()}:",
            'line'        => "${invitor_name} at ${program_name} just invited you the Personalized Care Management team!",
            'action_text' => 'Confirm and create a password',
            'action_link' => route('get.onboarding.create.invited.user', [
                'code' => $this->code,
            ]),
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
        $arr = $this->toArray($notifiable);

        $this->logInvite($notifiable);

        return (new MailMessage())
            ->subject($arr['subject'])
            ->greeting($arr['greeting'])
            ->line($arr['line'])
            ->action($arr['action_text'], $arr['action_link']);
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
