<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Contracts\HasAttachment;
use App\Contracts\LiveNotification;
use App\Models\Addendum;
use App\Note;
use App\Notifications\Channels\CircleLinkMailChannel;
use App\Services\NotificationService;
use App\Traits\ArrayableNotification;
use App\Traits\NotificationSubscribable;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class AddendumCreated extends Notification implements ShouldBroadcast, ShouldQueue, LiveNotification, HasAttachment
{
    use ArrayableNotification;
    use NotificationSubscribable;
    use Queueable;
    /**
     * @var
     */
    public $addendum;
    /**
     * @var Addendum
     */
    public $attachment;
    /**
     * @var User
     */
    private $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct(Addendum $addendum, User $sender)
    {
        $this->addendum = $addendum;
        $this->sender   = $sender;
    }

    public function attachmentType(): string
    {
        return Addendum::class;
    }

    public function dateForMail(): string
    {
        return Carbon::parse(now())->toDayDateTimeString();
    }

    public function description(): string
    {
        return 'Addendum';
    }

    /**
     * {@inheritdoc}
     */
    public function descriptionForMail(): string
    {
        return 'note';
    }

    public function emailLineStyled(): string
    { // @todo: maybe move this to a different interface
        $senderName         = $this->senderName();
        $descriptionForMail = $this->descriptionForMail();

        return "<a style='color: #376a9c'> $senderName </a> has commented on a <a style='color: #376a9c'> $descriptionForMail </a>";
    }

    /**
     * Returns an Eloquent model.
     */
    public function getAttachment(): ?Model
    {
        return $this->addendum;
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
        $senderName  = $this->senderName();
        $patientName = $this->getPatientName();

        return "<strong>$senderName</strong> responded to a note on $patientName";
    }

    /**
     * @param $notifiable
     * @return array
     */
    public function mailData($notifiable): array
    {
        return $this->dataForClhEmail($notifiable->email);
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

    public function senderId(): int
    {
        return $this->sender->id;
    }

    public function senderName(): string
    {
        return $this->sender->display_name;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
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
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $subjectLineStyled = $this->emailLineStyled();
        $emailData         = $this->mailData($notifiable);
        $unsubscribeLink   = $this->createUnsubscribeUrl($emailData['activityType']);

        return (new CircleLinkMailChannel($emailData, $unsubscribeLink))
            ->line($subjectLineStyled)
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
