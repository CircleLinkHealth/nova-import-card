<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Mail\TrixMailable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SendPatientEmail extends Notification
{
    use Queueable;

    protected $attachments;

    protected $content;

    /**
     * Create a new notification instance.
     *
     * @param mixed $content
     * @param mixed $filePathOrMedia
     * @param mixed $attachments
     */
    public function __construct($content, $attachments)
    {
        $this->content     = $content;
        $this->attachments = $attachments;
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
        //log media id from S3 so we can retrieve in the future
        return [
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return TrixMailable
     */
    public function toMail($notifiable)
    {
        return (new TrixMailable($this->content, $this->attachments))
            ->to($notifiable->email);
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
        return ['mail'];
    }
}
