<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResolveDisputeReminder extends Notification
{
    use Queueable;

    /**
     * Count of invoices required to be resolved.
     *
     * @var
     */
    public $disputes;

    /**
     * Create a new notification instance.
     *
     * @param $disputes
     */
    public function __construct($disputes)
    {
        $this->disputes = $disputes;
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('Reminder - Resolve Dispute Invoices')
            ->greeting('Hello,')
            ->line("There are {$this->disputes} Invoices disputes that required to be resolved")
            ->action('Resolve Disputes', url('superadmin/resources/disputes'))
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
