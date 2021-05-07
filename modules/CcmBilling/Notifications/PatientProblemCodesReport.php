<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class PatientProblemCodesReport extends Notification implements ShouldQueue
{
    use Queueable;

    protected int $mediaId;

    protected $signedUrl;

    /**
     * Create a new notification instance.
     *
     * @param int $mediaId
     */
    public function __construct(int $mediaId)
    {
        $this->mediaId = $mediaId;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
        ];
    }

    public function toDatabase($notifiable)
    {
        return
            [
                'media_id' => $this->mediaId,
            ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return MailMessage
     *@throws \Exception
     *
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('CLH Patient Problem Codes Report')
            ->greeting('Hello!')
            ->line('Your report is ready for download!')
            ->action('Download Report', $this->getSignedUrl($notifiable))
            ->line('Thank you for your patience!');

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
        if (isset($notifiable->id)) {
            return [
                'mail',
                'database',
            ];
        }

        return [
            'mail',
        ];
    }

    private function getSignedUrl($notifiable)
    {
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