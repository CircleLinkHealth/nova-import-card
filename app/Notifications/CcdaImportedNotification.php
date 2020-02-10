<?php

namespace App\Notifications;

use App\Contracts\HasAttachment;
use App\Contracts\LiveNotification;
use App\Traits\ArrayableNotification;
use CircleLinkHealth\Customer\AppConfig\PatientSupportUser;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CcdaImportedNotification extends Notification implements ShouldBroadcast, ShouldQueue, LiveNotification, HasAttachment
{
    use Queueable;
    use ArrayableNotification;
    /**
     * @var Ccda
     */
    protected $ccda;

    /**
     * Create a new notification instance.
     *
     * @param Ccda $ccda
     */
    public function __construct(Ccda $ccda)
    {
        $this->ccda = $ccda;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting('Hello')
            ->line('We would like to inform you that the CCDA(s) you uploaded earlied have been processed.')
            ->action('View Imported CCDAs', route('import.ccd.remix'))
            ->line('Thank you for using our CarePlan Manager!');
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
     * Gets the notification attachment type. eg. App\Models\Addendum.
     */
    public function attachmentType(): string
    {
        return Ccda::class;
    }

    /**
     * A string with the attachments name. eg. "Addendum".
     */
    public function description(): string
    {
        return "CCDA Imported";
    }

    public function getPatientName(): string
    {
        return '';
    }

    /**
     * A sentence to present the notification.
     */
    public function getSubject(): string
    {
        return "CCDA Imported";
    }

    public function noteId(): ?int
    {
        // TODO: Implement noteId() method.
    }

    /**
     * Redirect link to activity.
     */
    public function redirectLink(): string
    {
        return route('import.ccd.remix');
    }

    /**
     * User id who sends the notification.
     */
    public function senderId(): int
    {
        return PatientSupportUser::id();
    }

    public function senderName(): string
    {
        return 'CarePlan Manager';
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
        ]);
    }

    /**
     * Returns an Eloquent model.
     *
     * @return Model|null
     */
    public function getAttachment(): ?Model
    {
        return $this->ccda;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationData($notifiable): array
    {
        return $this->getNotificationData($notifiable);
    }
}
