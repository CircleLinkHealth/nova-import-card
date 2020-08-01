<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NurseInvoicesCreated extends Notification
{
    use Queueable;

    /**
     * @var string Link to cached view
     */
    public $link;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $link)
    {
        $this->link = $link;
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
        $url = str_replace(config('app.url'), config('opcache.url'), url($this->link));

        return (new MailMessage())
            ->greeting('Hello!')
            ->line('We would like to inform you that the nurse invoices you have requested are ready.')
            ->action('View Invoices', $url)
            ->line('Have a nice day!');
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
