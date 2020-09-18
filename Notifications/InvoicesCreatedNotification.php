<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Notifications;

use App\Contracts\LiveNotification;
use App\Traits\ArrayableNotification;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class InvoicesCreatedNotification extends Notification implements ShouldBroadcast, ShouldQueue, LiveNotification
{
    use ArrayableNotification;
    use Queueable;

    /**
     * @var Carbon
     */
    public $date;
    /**
     * @var string
     */
    public $description;
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
     * @var array
     */
    protected $practiceIds;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $mediaIds, Carbon $date, array $practiceIds)
    {
        $this->mediaIds    = implode(',', $mediaIds);
        $this->date        = $date;
        $this->practiceIds = $practiceIds;
    }

    /**
     * A string with the attachments name. eg. "Addendum".
     *
     * @param mixed $notifiable
     */
    public function description($notifiable): string
    {
        if ( ! $this->description) {
            $this->description = 'Practices: '.Practice::whereIn('id', $this->practiceIds)->pluck(
                'display_name'
            )->implode('display_name', ', ');
        }

        return $this->description;
    }

    /**
     * A sentence to present the notification.
     *
     * @param mixed $notifiable
     */
    public function getSubject($notifiable): string
    {
        return "The Invoices for {$this->getMonthYearText()} you had requested are ready.";
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
                'date'            => $this->date->toDateTimeString(),
                'month_year_text' => $this->getMonthYearText(),
                'media_ids'       => $this->mediaIds,
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
        $mail = (new MailMessage())
            ->subject("CPM {$this->getMonthYearText()} Invoices")
            ->greeting('Howdy there!');

        if (empty($this->mediaIds)) {
            return $mail
                ->line(
                    "Apologies. We did not generate any invoices because we did not have any data for {$this->getMonthYearText()}."
                )
                ->line('Thank you for using our CarePlan Manager!');
        }

        return $mail
            ->line($this->getSubject($notifiable))
            ->action('Download Invoices', $this->getSignedUrl($notifiable))
            ->line('For security reasons, this link will expire in 48 hours.')
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
        if (empty($this->mediaIds)) {
            return ['mail'];
        }

        return ['database', 'mail'];
    }

    private function getMonthYearText()
    {
        return "{$this->date->shortEnglishMonth} {$this->date->year}";
    }

    private function getSignedUrl($notifiable)
    {
        if ( ! $this->mediaIds) {
            return '';
        }
        if ( ! $this->signedUrl) {
            $this->signedUrl = URL::temporarySignedRoute(
                'download.zipped.media',
                now()->addDays(2),
                [
                    'user_id'   => $notifiable->id,
                    'media_ids' => $this->mediaIds,
                ]
            );
        }

        return $this->signedUrl;
    }
}
