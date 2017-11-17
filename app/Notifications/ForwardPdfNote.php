<?php

namespace App\Notifications;

use App\Channels\DirectMailChannel;
use App\Channels\FaxChannel;
use App\Note;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ForwardPdfNote extends Notification
{
    use Queueable;

    public $note;
    public $channels;
    public $pathToPdf;

    /**
     * Create a new notification instance.
     *
     * @param Note $note
     * @param array $channels
     */
    public function __construct(Note $note, $channels = [FaxChannel::class, DirectMailChannel::class, 'database'])
    {
        $this->note = $note;

        $this->channels = $channels;
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
        return $this->channels;
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
            'pathToPdf'    => $this->pathToPdf,
            'senderUserId' => auth()->user()->id,
        ];
    }

    /**
     * Get a pdf representation of the note
     *
     * @param $notifiable
     *
     * @return bool|string
     */
    public function toFax($notifiable)
    {
        if ( ! $notifiable || ! $notifiable->fax) {
            return false;
        }

        $this->pathToPdf = $this->note->toPdf();

        return $this->pathToPdf;
    }
}
