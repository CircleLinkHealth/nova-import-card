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
     * @return JsonResponse
     */
    public function getPatientName(): string
    {
        $patientId = $this->call->inbound_cpm_id;

        return NotificationService::getPatientName($patientId);
    }

    public function getSubject(): string
    {
        $activity = $this->call->sub_type;

        return "has assigned a $activity for";
    }

    public function redirectLink(): string
    {
        return route('patientCallList.index');
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
