<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReminder extends Notification
{
    use Queueable;
    /**
     * @var Carbon
     */
    protected $deadline;

    /**
     * Create a new notification instance.
     *
     * @param Carbon $deadline
     */
    public function __construct(Carbon $deadline)
    {
        $this->deadline = $deadline;
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
            ->subject('Reminder to review your invoice')
            ->greeting("Hello {$notifiable->first_name},")
            ->line("We would like to inform you that the deadline to submit a dispute for your invoice is on {$this->deadline->format('m-d-Y')} at {$this->deadline->format('h:iA T')}.")
            ->action('Review Invoice', url(route('care.center.invoice.review')))
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
