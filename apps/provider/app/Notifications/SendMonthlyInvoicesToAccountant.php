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
    /**
     * @var mixed
     */
    public $csvInvoices;

    /**
     * @var Carbon
     */
    public $date;

    /**
     * Create a new notification instance.
     *
     * @param mixed $csvInvoices
     */
    public function __construct(Carbon $date, $csvInvoices)
    {
        $this->date        = $date;
        $this->csvInvoices = $csvInvoices;
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
            ->greeting('Hello,')
            ->line("Please check attachment for: {$this->date->format('F Y')} Nurse Invoices")
            ->attachData($this->csvInvoices->getFile(), "Nurse_Invoices_Csv_{$this->date->format('F Y')}.csv")
            ->line('Thank you!');
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
