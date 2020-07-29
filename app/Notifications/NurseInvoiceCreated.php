<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Mail\NurseInvoiceMailer;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NurseInvoiceCreated extends Notification
{
    use Queueable;
    public $link;
    public $month;

    /**
     * Create a new notification instance.
     *
     * @param $link
     * @param $month
     */
    public function __construct($link, $month)
    {
        $this->link  = $link;
        $this->month = $month;
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

            'sender_id'    => auth()->user()->id ?? null,
            'sender_type'  => User::class,
            'sender_email' => 'no-reply@careplanmanager.com',

            'receiver_type'  => $notifiable->id,
            'receiver_id'    => get_class($notifiable),
            'receiver_email' => $notifiable->email,

            'subject'  => "{$this->month} Time and Fees Report",
            'pdf_path' => $this->link,
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
        return (new MailMessage())
            ->subject("{$this->month} Time and Fees Report")
            ->greeting("Hi {$notifiable->getFullName()},")
            ->line('Thanks for your efforts at CircleLink Health!')
            ->line('Attached please find a time receipt and calculation of fees payable to you for subject line hours.')
            ->line('Please let us know any questions or concerns. Funds will be transferred to you in the next few days.')
            ->attach(storage_path("download/{$this->link}"), [
                'as'   => "$this->month Invoice.pdf",
                'mime' => 'application/pdf',
            ]);
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
