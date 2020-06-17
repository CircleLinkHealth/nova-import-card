<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Contracts\LiveNotification;
use App\Traits\ArrayableNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class PatientActivityManagementEndOfMonthReportGeneratedNotification extends Notification implements ShouldBroadcast, ShouldQueue, LiveNotification
{
    use ArrayableNotification;
    use Queueable;

    /**
     * @var Carbon
     */
    public $date;

    /**
     * Media ID of the report.
     *
     * @var int
     */
    public $mediaId;

    /**
     * The signed URL to download the Media.
     *
     * @var string
     */
    public $signedUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $mediaId, Carbon $date)
    {
        $this->mediaId = $mediaId;
        $this->date    = $date;
    }

    /**
     * A string with the attachments name. eg. "Addendum".
     *
     * @param mixed $notifiable
     */
    public function description($notifiable): string
    {
        return '';
    }

    /**
     * A sentence to present the notification.
     *
     * @param mixed $notifiable
     */
    public function getSubject($notifiable): string
    {
        return "The Patient Activity Management Report for {$this->getMonthYearText()} is ready to download.";
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
                'date'            => $this->date->toDateString(),
                'month_year_text' => $this->getMonthYearText(),
                'media_ids'       => $this->mediaId,
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
            ->subject("CPM - Patient Activity Management End of Month Report for {$this->getMonthYearText()}")
            ->greeting('Howdy there!');

        if (empty($this->mediaId)) {
            return $mail
                ->line('Apologies. We were not able to generate a report. Please contact the dev team.')
                ->line('Thank you for using our CarePlan Manager!');
        }

        return $mail
            ->line($this->getSubject($notifiable))
            ->action('Download Report', $this->getSignedUrl($notifiable))
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
        return ['database', 'mail'];
    }

    private function getMonthYearText()
    {
        return "{$this->date->shortEnglishMonth} {$this->date->year}";
    }

    private function getSignedUrl($notifiable)
    {
        if ( ! $this->mediaId) {
            return '';
        }
        if ( ! $this->signedUrl) {
            $this->signedUrl = URL::temporarySignedRoute(
                'download.zipped.media',
                now()->addDays(2),
                [
                    'user_id'   => $notifiable->id,
                    'media_ids' => $this->mediaId,
                ]
            );
        }

        return $this->signedUrl;
    }
}
