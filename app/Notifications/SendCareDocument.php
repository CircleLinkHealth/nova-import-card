<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendCareDocument extends Notification
{
    use Queueable;

    private $channels = ['database'];
    private $media;
    private $patient;

    /**
     * Create a new notification instance.
     *
     * @param mixed $media
     * @param mixed $patient
     * @param mixed $channel
     */
    public function __construct($media, $patient, $channel)
    {
        $this->media = $media;

        $this->patient = $patient;

        //fix implementation
        $this->channels[] = $channel;
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
            'channels'   => $this->channels,
            'sender_id'  => auth()->user()->id,
            'patient_id' => $this->patient->id,
            'media_id'   => $this->media->id,
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
        $awvUrl = config('services.awv.url');

        $url = 'awv url + patient Id + report type from media + year to get report';

        return (new MailMessage())
            ->subject('subject')
            ->line('The introduction to the notification.')
            ->action('Notification Action', $url)
            ->line('Thank you for using our application!');
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
        return $this->channels;
    }
}
