<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendEnrollmentLetterToNonResponsivePatients extends Notification
{
    use Queueable;
    private $letter;
    /**
     * @var User
     */
    private $unresponsivePatient;

    /**
     * Create a new notification instance.
     *
     * @param $letter
     */
    public function __construct(User $unresponsivePatient, $letter)
    {
        $this->unresponsivePatient = $unresponsivePatient;
        $this->letter              = $letter;
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
//        Not using view. Will have to pass style, mob optimize etc.
        return (new MailMessage())
            ->greeting("Dear, $notifiable->first_name")
            ->line($this->letter)
            ->line('Our representative will be in touch witch you soon');
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
