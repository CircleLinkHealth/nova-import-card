<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Call;
use App\Contracts\PusherLiveNotifications;
use App\Services\NotificationService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CallCreated extends Notification implements ShouldBroadcast, ShouldQueue, PusherLiveNotifications
{
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
        $this->attachment = $this->call = $call;
        $this->sender     = $sender;
    }

    public function description(): string
    {
        return 'Activity';
    }

    /**
     * @return mixed
     */
    public function getAttachment()
    {
        return $this->attachment;
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

    public function getSubject(): string
    {
        $activity    = ! empty($this->call->sub_type) ? $this->call->sub_type : $this->call->type;
        $patientName = $this->getPatientName();

        return 'call' === $activity
            ? "Patient <strong>$patientName</strong> has a scheduled $activity"
            : "Patient <strong>$patientName</strong> requires a $activity"; //todo:write a migration to update this?
    }

    public function redirectLink(): string
    {
        $patientId = $this->getPatientId();

        return route('patient.careplan.print', ['patient' => $patientId]);
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
            $this->call->note_id,
            $this->getAttachment()->id,
            $this->redirectLink(),
            $this->description(),
            Call::class,
            $this->getSubject(),
            $this->sender->display_name
        );
    }

    /**
     *Get the broadcastable representation of the notification.
     *Returns by default -  ONLY the notification id & the notification type.
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
