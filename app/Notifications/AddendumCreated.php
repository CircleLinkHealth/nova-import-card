<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Contracts\LiveNotification;
use App\Models\Addendum;
use App\Note;
use App\Services\NotificationService;
use App\Traits\ArrayableNotification;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddendumCreated extends Notification implements ShouldBroadcast, ShouldQueue, LiveNotification
{
    use ArrayableNotification;
    use Queueable;

    public $addendum;
    public $attachment;
    /**
     * @var User
     */
    protected $sender;

    /**
     * Create a new notification instance.
     *
     * @param Addendum $addendum
     * @param User     $sender
     */
    public function __construct(Addendum $addendum, User $sender)
    {
        $this->attachment = $this->addendum = $addendum;
        $this->sender     = $sender;
    }

    /**
     * @return string
     */
    public function attachmentType(): string
    {
        return Addendum::class;
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

        return "<strong>$senderName</strong> responded to a note on $patientName"; //todo:write a migration to update this?
    }

    /**
     * @return int
     */
    public function noteId(): ?int
    {
        return $this->getAttachment()->addendumable_id;
    }

    /**
     * @return mixed
     */
    public function redirectLink(): string
    {
        $note = Note::where('id', $this->noteId())->first();

        return $note->link();
    }

    /**
     * @return int
     */
    public function senderId(): int
    {
        return $this->sender->id;
    }

    /**
     * @return string
     */
    public function senderName(): string
    {
        return $this->sender->display_name;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable): array
    {
        return $this->notificationData($notifiable);
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
        $senderName = $this->sender->display_name;

        return (new MailMessage())
            ->line("Dr. $senderName has commented on a note")
            ->action('View Comment', url($this->redirectLink()));
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
        return ['database', 'broadcast', 'mail'];
    }
}
