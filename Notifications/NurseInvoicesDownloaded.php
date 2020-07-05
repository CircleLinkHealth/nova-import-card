<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class NurseInvoicesDownloaded extends Notification
{
    use Queueable;
    /**
     * @var Carbon
     */
    private $date;
    /**
     * @var string
     */
    private $downloadFormat;

    /**
     * @var array
     */
    private $mediaIds;
    /**
     * @var
     */
    private $signedUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $mediaIds, Carbon $date, string $downloadFormat)
    {
        $this->mediaIds       = implode(',', $mediaIds);
        $this->date           = $date;
        $this->downloadFormat = $downloadFormat;
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
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
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
        $dateForMessage = Carbon::parse($this->date)->toDateString();
        if (empty($this->mediaIds)) {
            return (new MailMessage())
                ->line("No $this->downloadFormat were generated for $dateForMessage, for the selected practices");
        }

        return (new MailMessage())
            ->action("Download $this->downloadFormat Invoices", $this->getSignedUrl($notifiable))
            ->line('For security reasons, this link will expire in 48 hours.')
            ->line('Thank you for using our CarePlan!');
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

    private function getSignedUrl($notifiable)
    {
        if ( ! $this->mediaIds) {
            return '';
        }
        if ( ! $this->signedUrl) {
            $this->signedUrl = URL::temporarySignedRoute(
                'download.zipped.invoices',
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
