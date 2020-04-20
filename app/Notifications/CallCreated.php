<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Call;
use App\Contracts\HasAttachment;
use App\Contracts\LiveNotification;
use App\Services\NotificationService;
use App\Traits\ArrayableNotification;
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

class CallCreated extends Notification implements ShouldBroadcast, ShouldQueue, LiveNotification, HasAttachment
{
    use ArrayableNotification;
    use Queueable;

    public $attachment;
    /**
     * @var Call
     */
    private $call;
    /**
     * @var User
     */
    private $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct(Call $call, User $sender)
    {
        $this->call   = $call;
        $this->sender = $sender;
    }

    /**
     * @return string|null
     */
    public function activityType()
    {
        return ! empty($this->call->sub_type) ? $this->call->sub_type : $this->call->type;
    }

    public function attachmentType(): string
    {
        return Call::class;
    }

    public function dateForMail(): string
    {
//        return Carbon::parse(now())->toDayDateTimeString();
    }

    public function description($notifiable): string
    {
        return 'Activity';
    }

    public function descriptionForMail(): string
    {
//        return $this->activityType();
    }

    public function emailLineStyled(): string
    {
    }

    /**
     *  Attachments model.
     */
    public function getAttachment(): ?Model
    {
        return $this->call;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationData($notifiable): array
    {
        return $this->notificationData($notifiable);
    }

    /**
     * @return int
     */
    public function getPatientId()
    {
        return $this->call->inbound_cpm_id;
    }

    /**
     * @return JsonResponse
     */
    public function getPatientName(): string
    {
        $patientId = $this->getPatientId();

        return NotificationService::getPatientName($patientId);
    }

    public function getSubject($notifiable): string
    {
        $activity    = $this->activityType();
        $patientName = $this->getPatientName();

        return 'call' === $activity
            ? "Patient <strong>$patientName</strong> has a scheduled $activity"
            : "Patient <strong>$patientName</strong> requires a $activity";
    }

    /**
     * @param $notifiable
     */
    public function mailData($notifiable): array
    {
//        return $this->dataForClhEmail($notifiable->email);
    }

    public function noteId(): ?int
    {
        return $this->call->note_id;
    }

    public function redirectLink($notifiable): string
    {
        $patientId = $this->getPatientId();

        return route('patient.careplan.print', [$patientId]);
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
     */
    public function toArray($notifiable): array
    {
        return $this->notificationData($notifiable);
    }

    /**
     *Get the broadcastable representation of the notification.
     *Returns by default -  ONLY the notification id & the notification type.
     *
     * @param mixed $notifiable
     */
    public function toBroadcast($notifiable): BroadcastMessage
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
