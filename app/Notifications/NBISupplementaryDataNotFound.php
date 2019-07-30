<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NBISupplementaryDataNotFound extends Notification
{
    use Queueable;

    /**
     * @var User
     */
    protected $patientUser;

    /**
     * Create a new notification instance.
     *
     * @param User $patientUser
     */
    public function __construct(User $patientUser)
    {
        $this->patientUser = $patientUser;
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
            ->subject('NBI Patient Exception')
            ->line("We could not find patient with id: {$this->patientUser->id} in NBI's supplementary MRN list. Approval process has been locked <br>
Please visit this patient's profile page by clicking the button below.")
            ->action('Visit Page', route('patient.demographics.show', ['patientId' => $this->patientUser->id]));
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
