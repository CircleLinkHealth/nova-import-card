<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Notifications;

use CircleLinkHealth\Core\Contracts\HasAttachment;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use CircleLinkHealth\NurseInvoices\Helpers\NurseInvoiceDisputeDeadline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReviewInitialReminder extends Notification implements ShouldQueue, HasAttachment
{
    use Queueable;

    /**
     * @var NurseInvoice
     */
    protected $attachment;

    /**
     * Create a new notification instance.
     */
    public function __construct(NurseInvoice $invoice)
    {
        $this->attachment = $invoice;
    }

    /**
     * Returns an Eloquent model.
     */
    public function getAttachment(): ?Model
    {
        return $this->attachment;
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
            'invoice' => $this->attachment,
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
        $month = $this->attachment->month_year->format('F Y');

        return (new MailMessage())
            ->subject("Your $month Invoice from CircleLink Health")
            ->greeting("Hello {$notifiable->first_name},")
            ->line('Thank you for using CarePlan Manager for providing care!')
            ->line("Please click below button to review your invoice for $month")
            ->action('Review Invoice', url(route('care.center.invoice.review')))
            ->line((new NurseInvoiceDisputeDeadline($this->attachment->month_year))->warning());
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
