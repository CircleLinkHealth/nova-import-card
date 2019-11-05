<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Contracts\PusherLiveNotifications;
use App\Models\Addendum;
use App\Note;
use App\Services\NotificationService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddendumCreated extends Notification implements ShouldBroadcast, ShouldQueue, PusherLiveNotifications
{
    use Queueable;
    public $addendum;
    public $attachment;
    /**
     * @var User
     */
    protected $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct(Addendum $addendum, User $sender)
    {
        $this->attachment = $this->addendum = $addendum;
        $this->sender     = $sender;
    }

    public function description(): string
    {
        return 'Addendum';
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
    public function getPatientName(): string
    {
        $patientId = $this->getPatientId();

        return NotificationService::getPatientName($patientId);
    }

    public function getSubject(): string
    {
        $senderName  = $this->sender->display_name;
        $patientName = $this->getPatientName();

        return "<strong>$senderName</strong> responded to a note on $patientName"; //todo:write a migration to update this
    }

    /**
     * @return mixed
     */
    public function redirectLink(): string
    {
        $note = Note::where('id', $this->getNoteId())->first();

        return $note->link();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toArray($notifiable): array
    {
        return NotificationService::getNotificationArrayRepresentation(
            $notifiable,
            $this->sender->id,
            $this->getPatientName(),
            $this->getNoteId(),
            $this->getAttachment()->id,
            $this->redirectLink(),
            $this->description(),
            Addendum::class,
            $this->getSubject(),
            $this->sender->display_name
        );
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
    public function toBroadcast($notifiable): object
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
