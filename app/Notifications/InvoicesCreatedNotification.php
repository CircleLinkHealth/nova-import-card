<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Contracts\LiveNotification;
use App\Traits\ArrayableNotification;
use App\Traits\NotificationSubscribable;
use Carbon\Carbon;
use CircleLinkHealth\Customer\AppConfig\PatientSupportUser;
use CircleLinkHealth\Customer\Entities\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicesCreatedNotification extends Notification implements ShouldBroadcast, ShouldQueue, LiveNotification
{
    use ArrayableNotification;
    use NotificationSubscribable;
    use Queueable;
    /**
     * @var Carbon
     */
    public $date;
    /**
     * Comma delimited string of Media IDs.
     *
     * @var string
     */
    public $mediaIds;

    /**
     * The signed URL to download the Media.
     *
     * @var string
     */
    public $signedUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $media, Carbon $date)
    {
        $this->mediaIds = $media;
        $this->date     = $date;
    }

    /**
     * Gets the notification attachment type. eg. App\Models\Addendum.
     */
    public function attachmentType(): string
    {
        return Media::class;
    }

    /**
     * A string with the attachments name. eg. "Addendum".
     */
    public function description(): string
    {
        return 'The Invoices you requested are ready.';
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
        return 'The Invoices you requested are ready.';
    }

    public function noteId(): ?int
    {
        return null;
    }

    /**
     * Redirect link to activity.
     */
    public function redirectLink(): string
    {
        return $this->getSignedUrl();
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
        return 'CircleLink Health Invoicing';
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            'date'      => $this->date->toDateTimeString(),
            'media_ids' => $this->mediaIds,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * NOTE: The `notification_id` and `notification_type` are automatically included by default.
     *
     * @param mixed $notifiable
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([]);
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
        $invoicesMonthYear = "{$this->date->shortEnglishMonth} {$this->date->year}";

        $mail = (new MailMessage())
            ->subject("CPM $invoicesMonthYear Invoices")
            ->greeting('Howdy there!');

        if (empty($this->mediaIds)) {
            return $mail
                ->line("Apologies. We did not generate any invoices because we did not have any data for $invoicesMonthYear.")
                ->line('Thank you for using our CarePlan Manager!');
        }

        return $mail
            ->line("The invoices for $invoicesMonthYear you had requested are ready.")
            ->action('Download Invoices', $this->getSignedUrl())
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
        return ['database', 'mail', 'broadcast'];
    }

    private function getSignedUrl($notifiable)
    {
        if ( ! $this->signedUrl) {
            $this->signedUrl = \URL::temporarySignedRoute('download.zipped.media', now()->addDays(2), [$notifiable->id, $this->mediaIds]);
        }

        return $this->signedUrl;
    }
}
