<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EhrReportWriterNotification extends Notification
{
    use Queueable;

    protected $practiceName;

    protected $text;

    /**
     * Create a new notification instance.
     *
     * @param mixed $text
     * @param mixed $practiceName
     */
    public function __construct($text, $practiceName)
    {
        $this->text         = $text;
        $this->practiceName = $practiceName;
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
        return (new MailMessage())
            ->subject("Eligibility Results for {$this->practiceName} batch.")
            ->line("{$this->text}");
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
