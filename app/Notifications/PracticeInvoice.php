<?php

namespace App\Notifications;


use App\Mail\PracticeInvoice as PracticeInvoiceMailable;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PracticeInvoice extends Notification
{
    use Queueable;


    /**
     * The link passed to the view
     *
     * For an example @see: PracticeInvoiceController, method send
     *
     */
    protected $invoiceLink;


    /**
     * The attachment to the Mailable
     *
     * For an example @see: PracticeInvoiceController, method send
     *
     */
    protected $filePath;


    /**
     * Create a new notification instance.
     *
     * @param $invoiceLink
     * @param $filePath
     */
    public function __construct($invoiceLink, $filePath)
    {
        $this->invoiceLink = $invoiceLink;

        $this->filePath = $filePath;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'mail',
            'database',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return PracticeInvoiceMailable
     */
    public function toMail(User $notifiable)
    {
        return (new PracticeInvoiceMailable($this->invoiceLink, $this->filePath))
            ->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function toDatabase($notifiable)
    {
        return
            [
                'invoiceLink' => $this->invoiceLink,
            ];
    }
}
