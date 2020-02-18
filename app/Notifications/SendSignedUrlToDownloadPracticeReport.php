<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendSignedUrlToDownloadPracticeReport extends Notification implements ShouldQueue
{
    use Queueable;
    /**
     * @var string
     */
    public $signedLink;

    /**
     * @var int
     */
    protected $mediaId;

    /**
     * @var int
     */
    protected $practiceId;

    /**
     * @var string
     */
    protected $filename;
    
    /**
     * Create a new notification instance.
     *
     * @param string $filename
     * @param string $signedUrl
     * @param int $practiceId
     * @param int $mediaId
     */
    public function __construct(string $filename, string $signedUrl, int $practiceId, int $mediaId)
    {
        $this->signedLink = $signedUrl;
        $this->filename   = $filename;
        $this->practiceId = $practiceId;
        $this->mediaId    = $mediaId;
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
            'filename' => $this->filename,
            'practice_id'  => $this->practiceId,
            'media_id'     => $this->mediaId,
        ];
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
            ->subject('Your report from CircleLink Health')
            ->line('Please click the View Report button below to see the report you requested from CircleLink Health.')
            ->line('You will be redirected to CarePlan Manager, and required to login beforehand.')
            ->line('For security, the link will expire in 48 hours. If you need a new link, please contact CircleLink Health.')
            ->action('View Report', url($this->signedLink))
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
