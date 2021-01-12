<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportGenerated extends Notification
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
     * @var string
     */
    private $reportName;

    /**
     * Create a new notification instance.
     */
    public function __construct(Carbon $date, string $url, string $reportName)
    {
        $this->date       = $date;
        $this->url        = $url;
        $this->reportName = $reportName;
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
            'date'       => $this->date->toDateTimeString(),
            'url'        => $this->url,
            'reportName' => $this->reportName,
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
                'We would like to inform you that a '.$this->reportName.' was generated for '.presentDate($this->date)
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
        return ['database', 'mail'];
    }
}
