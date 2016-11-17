<?php

namespace App\Notifications;

use App\MailLog;
use App\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewNote extends Notification
{
    use Queueable;

    protected $message;
    protected $url;

    /**
     * Create a new notification instance.
     *
     * @param Message $cpmMessage
     */
    public function __construct(
        MailLog $cpmMessage,
        $noteUrl
    ) {
        $this->message = $cpmMessage;
        $this->url = $noteUrl;
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->greeting($this->message->body)
            ->subject($this->message->subject)
            ->cc('raph@circlelinkhealth.com')
            ->line('Click Below to see Note')
            ->action('View Note', $this->url);
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
}
