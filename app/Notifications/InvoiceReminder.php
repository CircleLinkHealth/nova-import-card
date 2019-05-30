<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
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
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('Reminder - Invoice Review')
            ->greeting('Hello,')
            ->line('Invoice Review Period will end in 30hrs, please take some time to review your Invoice if you havent done it already')
            ->action('Review Here', url(route('care.center.invoice.review')))
            ->line('Thank you for using CarePlan Manager for providing care!');
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
