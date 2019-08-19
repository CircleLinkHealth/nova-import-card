<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Http\Controllers\NotificationController;
use App\Models\Addendum;
use App\Note;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddendumCreated extends Notification implements ShouldBroadcast, ShouldQueue
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

    /**
     * @return JsonResponse
     */
    public function getPatientName()
    {
        $patientId = $this->getPatientId();

        return NotificationService::getPatientName($patientId);
    }

    /**
     * @return mixed
     */
    public function redirectLink()
    {
        $note = Note::where('id', $this->getNoteId())->first();

        return $note->link();
    }

    /**
     * Get the array representation of the notification.
     * $notifiable = User who wrote the note.
     * This function should contain the same data(key => values) in all notifications.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'sender_id'       => auth()->id(),
            'receiver_id'     => $notifiable->id,
            'patient_name'    => $this->getPatientName(),
            'note_id'         => $this->getNoteId(),
            'attachment_id'   => $this->getAttachment()->id,
            'redirect_link'   => $this->redirectLink(),
            'attachment_type' => Addendum::class,
            'description'     => 'Addendum',
            'subject'         => 'has created an addendum for',
            'sender_name'     => auth()->user()->display_name,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * Returns by default -  ONLY the notification id & the notification type
     *
     * @param mixed $notifiable
     *
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
        ]);
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

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }
}
