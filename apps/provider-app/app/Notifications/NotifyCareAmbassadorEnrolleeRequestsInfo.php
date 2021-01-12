<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\CareAmbassador;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifyCareAmbassadorEnrolleeRequestsInfo extends Notification
{
    use Queueable;
    /**
     * @var
     */
    private $enrollable;

    /**
     * Create a new notification instance.
     *
     * @param $enrollable
     */
    public function __construct($enrollable)
    {
        $this->enrollable = $enrollable;
    }

    public function attachmentType(): string
    {
        return CareAmbassador::class;
    }

    /**
     * Returns an Eloquent model.
     */
    public function getAttachment(): ?Model
    {
        return $this->enrollable;
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
            'enrollable_id' => $this->enrollable->id,
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
        $enrollableId = $this->enrollable->id;

        return (new MailMessage())
            ->line("Enrollee Patient $enrollableId")
            ->line('requested a call to enquire information about the enrollment process')
            ->action('See patients details', url(route('enrollee.to.call.details', ['enrollable_id' => $enrollableId])));
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
        return ['database', 'mail'];
    }
}
