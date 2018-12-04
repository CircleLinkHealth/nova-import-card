<?php

namespace App\Notifications;

use App\Mail\PracticeInvoice as PracticeInvoiceMailable;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PracticeInvoice extends Notification implements ShouldQueue
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
        if (isset($notifiable->id)) {
            return [
                'mail',
                'database',
            ];
        }

        return [
            'mail',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return PracticeInvoiceMailable
     */
    public function toMail($notifiable)
    {
        if (isset($notifiable->email)) {
            $to = $notifiable->email;
        }

        if (isset($notifiable->routes['mail'])) {
            $to = $notifiable->routes['mail'];
        }

        return (new PracticeInvoiceMailable($this->invoiceLink, $this->filePath))
            ->to($to);
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
