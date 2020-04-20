<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NurseDailyReportGenerated extends Notification
{
    use Queueable;
    /**
     * The date the report was generated for.
     *
     * @var Carbon
     */
    public $date;
    /**
     * The url to download the media file.
     *
     * @var
     */
    public $url;

    /**
     * Create a new notification instance.
     *
     * @param $url
     */
    public function __construct(Carbon $date, $url)
    {
        $this->date = $date;
        $this->url  = $url;
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
            'date' => $this->date->toDateTimeString(),
            'url'  => $this->url,
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
            ->greeting('Hello!')
            ->line(
                'We would like to inform you that a "Nurse Daily Report" was generated for '.presentDate($this->date)
            )
            ->action('Download Report', $this->url)
            ->line('Have a nice day!');
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
        return ['mail', 'database'];
    }
}
