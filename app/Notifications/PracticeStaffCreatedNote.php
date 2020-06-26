<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Contracts\HasAttachment;
use App\Contracts\LiveNotification;
use App\Note;
use App\Traits\ArrayableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PracticeStaffCreatedNote extends Notification implements LiveNotification, HasAttachment, ShouldQueue, ShouldBroadcast
{
    use ArrayableNotification;
    use Queueable;
    /**
     * @var Note
     */
    public $note;

    /**
     * Create a new notification instance.
     */
    public function __construct(Note $note)
    {
        $this->note = $note;
    }

    /**
     * {@inheritdoc}
     */
    public function description($notifiable): string
    {
        return 'Click the button below to view the note.';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachment(): ?Model
    {
        return $this->note;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject($notifiable): string
    {
        return "{$this->note->author->getFullName()} wrote a note about one of your patients";
    }

    /**
     * {@inheritdoc}
     */
    public function redirectLink($notifiable): string
    {
        return $this->note->link();
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            'subject'      => $this->getSubject($notifiable),
            'description'  => $this->description($notifiable),
            'redirectLink' => $this->redirectLink($notifiable),
            'noteId'       => $this->note->id,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([]);
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
            ->subject($this->getSubject($notifiable))
            ->line($this->description($notifiable))
            ->action('View Note', $this->redirectLink($notifiable))
            ->line('Thank you for using our application!');
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
        return ['database', 'broadcast', 'mail'];
    }
}
