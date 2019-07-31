<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Contracts\PusherNotification;
use App\Models\Addendum;
use App\Services\AddendumNotificationsService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddendumCreated extends Notification implements PusherNotification
{
    use Queueable;
    public $addendum;
    public $attachment;

    /**
     * Create a new notification instance.
     *
     * @param Addendum $addendum
     */
    public function __construct(Addendum $addendum)
    {
        $this->attachment = $this->addendum = $addendum;
    }

    /**
     * @return mixed
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @return mixed
     */
    public function getNoteId()
    {
        return $this->getAttachment()->addendumable_id;
    }

    /**
     * Get patient_id that the addendum was written for.
     *
     * @return mixed
     */
    public function getPatientId(): ?int
    {
        return $this->getAttachment()->addendumable->patient_id;
    }

    public function getUrlToRedirectUser(): ?string
    {
        return session()->previousUrl();
    }

    /**
     * Get the array representation of the notification.
     * $notifiable = User who wrote the note.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'sender_id'     => auth()->id(),
            'receiver_id'   => $notifiable->id,
            'patient_id'    => $this->getPatientId(),
            'note_id'       => $this->getNoteId(),
            'attachment_id' => $this->getAttachment()->id,
            //            'redirectTo'      => AddendumNotificationsService::getUrlToRedirectUser(),
            'attachment_type' => Addendum::class,
            'description'     => AddendumNotificationsService::ADDENDUM_DESCRIPTION,
            'subject'         => AddendumNotificationsService::ADDENDUM_SUBJECT,
        ];
    }

    public function toDatabase($notifiable)
    {
        return array_merge([
            'patient_name' => $this->addendum->addendumable->patient->display_name,
            'sender_name'  => auth()->user()->display_name,
        ], $this->toArray($notifiable));
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
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    public function toPusher(): \App\ValueObjects\PusherNotification
    {
        return new \App\ValueObjects\PusherNotification(
            auth()->id(),
            $this->notifiable_id,
            $this->getPatientId(),
            optional($this->attachment->id),
            $this->attachment_id,
            $this->getUrlToRedirectUser(),
            $this->attachment_type,
            'Addendum',
            'has created an addendum for'
        );
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
        return ['database'];
    }
}
