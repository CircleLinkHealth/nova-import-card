<?php

namespace App\Notifications;

use App\Mail\NoteForwarded;
use App\MailLog;
use App\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
     * @return mixed
     */
    public function toMail($notifiable)
    {
        return (new NoteForwarded($this->message, $this->url));
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
