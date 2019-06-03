<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendMonthlyInvoicesToAccountant extends Notification
{
    use Queueable;

    public $date;
    public $media;

    /**
     * Create a new notification instance.
     *
     * @param Carbon $date
     * @param mixed  $media
     */
    public function __construct(Carbon $date, $media)
    {
        $this->date  = $date;
        $this->media = $media;
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
            ->attachData($this->media->getFile(), "Nurse_Invoices_Csv_{$this->date->format('F Y')}.csv")
            ->line('The introduction to the notification.')
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
        return ['mail', 'database'];
    }
}
