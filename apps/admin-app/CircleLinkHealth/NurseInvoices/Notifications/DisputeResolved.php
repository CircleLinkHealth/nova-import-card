<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Notifications;

use CircleLinkHealth\SharedModels\Entities\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DisputeResolved extends Notification
{
    use Queueable;

    /**
     * @var Dispute
     */
    public $dispute;

    /**
     * Create a new notification instance.
     *
     * @param mixed $startDate
     */
    public function __construct(Dispute $dispute)
    {
        $this->dispute = $dispute;
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
            ->subject('Your invoice dispute has been resolved')
            ->greeting("Hello {$notifiable->first_name},")
            ->line('We would like to inform  you that your invoice dispute has been resolved. Please see below message from CircleLink Health:')
            ->line('"'.$this->dispute->resolution_note.'"')
            ->action('See Invoice', url(route('care.center.invoice.review')));
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
