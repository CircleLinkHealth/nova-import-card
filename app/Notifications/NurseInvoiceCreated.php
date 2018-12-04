<?php

namespace App\Notifications;

use App\Mail\NurseInvoiceMailer;
use App\User;
use Illuminate\Bus\Queueable;
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
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return NurseInvoiceMailer
     */
    public function toMail($notifiable)
    {
        return (new NurseInvoiceMailer($notifiable->getFullName(), $this->link, $this->month))
            ->to($notifiable->email, $notifiable->getFullName());
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
            'channels' => $this->via($notifiable),

            'sender_id'    => auth()->user()->id,
            'sender_type'  => User::class,
            'sender_email' => 'no-reply@circlelinkhealth.com',

            'receiver_type'  => $notifiable->id,
            'receiver_id'    => get_class($notifiable),
            'receiver_email' => $notifiable->email,

            'subject'   => "$this->month Time and Fees Report",
            'pathToPdf' => $this->link,
        ];
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
        return ['mail', 'database'];
    }
}
