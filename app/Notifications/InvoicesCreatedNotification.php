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
     *
     * @param array $media
     */
    public function __construct(array $mediaIds, Carbon $date)
    {
        $this->mediaIds = implode(',', $mediaIds);
        $this->date     = $date;
    }

    /**
     * A string with the attachments name. eg. "Addendum".
     *
     * @param mixed $notifiable
     */
    public function description($notifiable): string
    {
        return 'The Invoices you requested are ready.';
    }

    /**
     * A sentence to present the notification.
     *
     * @param mixed $notifiable
     */
    public function getSubject($notifiable): string
    {
        return 'The Invoices you requested are ready.';
    }

    /**
     * Redirect link to activity.
     *
     * @param null $notifiable
     */
    public function redirectLink($notifiable): string
    {
        return $this->getSignedUrl($notifiable);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toArray($notifiable): array
    {
        return array_merge(
            $this->notificationData($notifiable),
            [
                'date'      => $this->date->toDateTimeString(),
                'media_ids' => $this->mediaIds,
            ]
        );
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
                ->line(
                    "Apologies. We did not generate any invoices because we did not have any data for $invoicesMonthYear."
                )
                ->line('Thank you for using our CarePlan Manager!');
        }

        return $mail
            ->line("The invoices for $invoicesMonthYear you had requested are ready.")
            ->action('Download Invoices', $this->getSignedUrl($notifiable))
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
            $this->signedUrl = \URL::temporarySignedRoute(
                'download.zipped.media',
                now()->addDays(2),
                [$notifiable->id, $this->mediaIds]
            );
        }

        return $this->signedUrl;
    }
}
