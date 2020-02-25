<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifyDownloadMediaCollection extends Notification
{
    use Queueable;

    protected $url;

    /**
     * Create a new notification instance.
     *
     * @param mixed $collectionName
     *
     * @return void
     */
    public function __construct($collectionName)
    {
        $this->url = route('download.collection-as-zip', ['collectionName' => $collectionName]);
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
            'url' => $this->url,
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
            ->subject('Patient Consent Letters')
            ->line('Click button below to be redirected to CarePlan Manager, where you may download the consent letters.')
            ->action('Download Consent Letters', $this->url)
            ->line('Regards, the CircleLink Health Team');
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
}
