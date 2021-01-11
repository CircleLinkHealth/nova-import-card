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
    public $deadline;
    /**
     * @var Carbon
     */
    public $invoiceMonth;

    /**
     * Create a new notification instance.
     */
    public function __construct(Carbon $deadline, Carbon $invoiceMonth)
    {
        $this->deadline     = $deadline;
        $this->invoiceMonth = $invoiceMonth;
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
            ->subject('Last chance to review your invoice')
            ->greeting("Hello {$notifiable->first_name},")
            ->line("We would like to inform you that the deadline to submit a dispute for your invoice for {$this->invoiceMonth->format('F Y')} is on {$this->deadline->format('m-d-Y')} at {$this->deadline->format('h:iA T')}.")
            ->line('Please take some time to review your invoice, in case you haven\'t yet.')
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
        return ['database', 'mail'];
    }
}
