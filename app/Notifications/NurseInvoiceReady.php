<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NurseInvoiceReady extends Notification implements ShouldQueue
{
    use Queueable;

    protected $startDate;
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @param mixed $startDate
     * @param mixed $user
     */
    public function __construct($startDate, $user)
    {
        $this->startDate = $startDate;
        $this->user      = $user;
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
            ->subject('Invoice Generated')
            ->greeting("Hello, {$notifiable->first_name}")
            ->line("Click below button to review your invoice for {$this->startDate->format('F Y')}")
            ->action('Review Here', url(route('care.center.invoice.review')))
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
