<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReviewInitialReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $startDate;
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @param mixed $startDate
     */
    public function __construct(Carbon $startDate)
    {
        $this->startDate = $startDate;
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
            ->subject("Review {$this->startDate->format('F Y')} Invoice")
            ->greeting("Hello {$notifiable->first_name},")
            ->line('Thank you for using CarePlan Manager for providing care!')
            ->line("Please click below button to review your invoice for {$this->startDate->format('F Y')}")
            ->action('Review Invoice', url(route('care.center.invoice.review')));
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
