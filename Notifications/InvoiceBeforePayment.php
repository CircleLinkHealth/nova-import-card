<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Notifications;

use App\Contracts\HasAttachment;
use App\Mail\NurseInvoiceMailer;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceBeforePayment extends Notification implements HasAttachment, ShouldQueue
{
    use Queueable;
    public $invoice;
    public $media;

    /**
     * Create a new notification instance.
     *
     * @param $invoice
     * @param $media
     */
    public function __construct(NurseInvoice $invoice, Media $media)
    {
        $this->invoice = $invoice;
        $this->media   = $media;
    }

    /**
     * Returns an Eloquent model.
     */
    public function getAttachment(): ?Model
    {
        return $this->invoice;
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
            'channels' => $this->via($notifiable),
            'media'    => $this->media->id,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return NurseInvoiceMailer
     */
    public function toMail($notifiable)
    {
        $date = $this->invoice->month_year->format('F Y');
        $name = $notifiable->getFullName();

//        @todo: find a cleaner way
        $path = storage_path("tmp/{$this->media->id}_{$this->media->collection_name}_{$date}");
        $done = file_put_contents($path, $this->media->getFile());

        if ($done) {
            return (new MailMessage())
                ->subject("$date Time and Fees Report")
                ->greeting("Hi $name,")
                ->line('Thanks for your efforts at CircleLink Health!')
                ->line('Attached please find a time receipt and calculation of fees payable to you for subject line hours.')
                ->line('Please let us know any questions or concerns. Funds will be transferred to you in the next few days.')
                ->attach($path, [
                    'as'   => "$name - $date Invoice.pdf",
                    'mime' => 'application/pdf',
                ]);
        }

        \Log::error("Not sending notification. File not found. Invoice:{$this->invoice->id}");
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
