<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Contracts\LiveNotification;
use App\Traits\ArrayableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendSignedUrlToDownloadPracticeReport extends Notification implements ShouldQueue, LiveNotification
{
    use ArrayableNotification;
    use Queueable;
    /**
     * @var string
     */
    public $signedLink;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var int
     */
    protected $mediaId;

    /**
     * @var int
     */
    protected $practiceId;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $filename, string $signedUrl, int $practiceId, int $mediaId)
    {
        $this->signedLink = $signedUrl;
        $this->filename   = $filename;
        $this->practiceId = $practiceId;
        $this->mediaId    = $mediaId;
    }

    /**
     * {@inheritdoc}
     */
    public function description($notifiable): string
    {
        return 'For security, the link will expire in 48 hours. If you need a new link, please contact CircleLink Health.';
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject($notifiable): string
    {
        return 'Your report from CircleLink Health';
    }

    /**
     * {@inheritdoc}
     */
    public function redirectLink($notifiable): string
    {
        return url($this->signedLink);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            'filename'    => $this->filename,
            'practice_id' => $this->practiceId,
            'media_id'    => $this->mediaId,
        ];
    }

    /**
     * {@inheritdoc}
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
        return (new MailMessage())
            ->subject($this->getSubject($notifiable))
            ->line('Please click the View Report button below to see the report you requested from CircleLink Health.')
            ->line('You will be redirected to CarePlan Manager, and required to login beforehand.')
            ->line($this->description($notifiable))
            ->action('View Report', $this->redirectLink($notifiable))
            ->line('Thank you for choosing CircleLink Health!');
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
}
