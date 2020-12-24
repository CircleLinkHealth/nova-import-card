<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Carbon\Carbon;
use CircleLinkHealth\Core\Contracts\HasAttachment;
use CircleLinkHealth\Core\Contracts\LiveNotification;
use CircleLinkHealth\Customer\Entities\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class CarePlansGeneratedNotification extends Notification implements ShouldBroadcast, ShouldQueue, LiveNotification, HasAttachment
{
    use Queueable;
    private Carbon $dateRequested;

    private ?int $mediaId;
    private ?string $signedUrl;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(?int $mediaId, Carbon $dateRequested)
    {
        $this->mediaId       = $mediaId;
        $this->dateRequested = $dateRequested;
        $this->signedUrl     = null;
    }

    public function description($notifiable): string
    {
        $dateForMessage = Carbon::parse($this->dateRequested)->format('m/d/Y H:i');

        return "Date: $dateForMessage";
    }

    public function getAttachment(): ?Model
    {
        return Media::find($this->mediaId);
    }

    public function getSubject($notifiable): string
    {
        return 'Care Plans are ready to be printed. Click here to download.';
    }

    public function notificationData($notifiable): array
    {
        return [
            'redirect_link' => $this->redirectLink($notifiable),
            'description'   => $this->description($notifiable),
            'subject'       => $this->getSubject($notifiable),
        ];
    }

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
        return array_merge($this->notificationData($notifiable), [
            'media_id'       => $this->mediaId,
            'date_requested' => $this->dateRequested,
        ]);
    }

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
        $dateForMessage = Carbon::parse($this->dateRequested)->format('m/d/Y H:i');
        if (empty($this->mediaId)) {
            return (new MailMessage())
                ->line("No care plans were generated for your request at $dateForMessage");
        }

        return (new MailMessage())
            ->action('Download Care Plans', $this->getSignedUrl($notifiable))
            ->line("At $dateForMessage, you requested to generate care plans.")
            ->line('For security reasons, this link will expire in 48 hours.')
            ->line('Thank you for using CarePlan Manager!');
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
        // todo: broadcast not working for some reason
        return ['database', 'mail'];
    }

    private function getSignedUrl($notifiable)
    {
        if (empty($this->mediaId)) {
            return '';
        }
        if (empty($this->signedUrl)) {
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
