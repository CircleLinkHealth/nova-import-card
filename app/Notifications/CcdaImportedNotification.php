<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use CircleLinkHealth\Core\Contracts\HasAttachment;
use App\Contracts\LiveNotification;
use App\Traits\ArrayableNotification;
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
    use ArrayableNotification;
    use Queueable;

    /**
     * @var Ccda
     */
    protected $ccda;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ccda $ccda)
    {
        $this->ccda = $ccda;
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
     *
     * @param mixed $notifiable
     */
    public function description($notifiable): string
    {
        return 'CCDA Imported';
    }

    /**
     * Returns an Eloquent model.
     */
    public function getAttachment(): ?Model
    {
        return $this->ccda;
    }

    /**
     * A sentence to present the notification.
     *
     * @param mixed $notifiable
     */
    public function getSubject($notifiable): string
    {
        return 'CCDA Imported';
    }

    /**
     * Redirect link to activity.
     *
     * @param mixed $notifiable
     */
    public function redirectLink($notifiable): string
    {
        return route('import.ccd.remix');
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
            ->greeting('Hello')
            ->line('We would like to inform you that the CCDA(s) you uploaded earlied have been processed.')
            ->action('View Imported CCDAs', route('import.ccd.remix'))
            ->line('Thank you for using our CarePlan Manager!');
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
