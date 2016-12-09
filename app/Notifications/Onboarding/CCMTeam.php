<?php

namespace App\Notifications\Onboarding;

use App\MailLog;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/*
 * BODY:

    Subject: [Implementation lead name] Invited You to the Personalized Care Management Team with CircleLink!

    Dear [user name],

    [Implementation lead name] at [organization name] just invited you the Personalized Care Management team!

    Please click here to confirm and create a password. [here is linked to only pg 1 of onboarding pages without Provider checkbox, and with pre-filled except password]

    Welcome aboard!
    CircleLink Team

 */

class CCMTeam extends Notification
{
    use Queueable;

    private $sender;
    private $program;
    private $link;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        User $implementationLead,
        $url
    ) {
        $this->sender = $implementationLead;
        $this->program = $this->sender->primaryProgram;
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

        $invitor_name = $this->sender->fullName;
        $program_name = $this->program->display_name;

        return (new MailMessage)
            ->subject("$invitor_name Invited You to the Personalized Care Management Team with CircleLink!")
            ->greeting("Dear $notifiable->fullName:")
            ->line(" $invitor_name at $program_name just invited you the Personalized Care Management team!")
            ->action('Please click here to confirm and create a password.', $this->link);
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
