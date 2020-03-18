<?php

namespace App\Notifications;

use App\Contracts\HasAttachment;
use App\Contracts\LiveNotification;
use App\Note;
use App\Traits\ArrayableNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PracticeStaffCreatedNote extends Notification implements LiveNotification, HasAttachment, ShouldQueue
{
    use ArrayableNotification;
    use Queueable;
    /**
     * @var Note
     */
    protected $note;
    
    /**
     * Create a new notification instance.
     *
     * @param Note $note
     */
    public function __construct(Note $note)
    {
        $this->note = $note;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->getSubject($notifiable))
                    ->line($this->description($notifiable))
                    ->action('View Note', $this->redirectLink($notifiable))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable):array
    {
        return [
            'subject' => $this->getSubject($notifiable),
            'description' => $this->description($notifiable),
            'redirectLink' => $this->redirectLink($notifiable),
            'noteId' => $this->note->id,
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function description($notifiable): string
    {
        return 'Click the button below to view the note.';
    }
    
    /**
     * @inheritDoc
     */
    public function getSubject($notifiable): string
    {
        return "{$this->note->author->getFullName()} wrote a note about one of your patients";
    }
    
    /**
     * @inheritDoc
     */
    public function redirectLink($notifiable): string
    {
        return $this->note->link();
    }
    
    /**
     * @inheritDoc
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([]);
    }
    
    /**
     * @inheritDoc
     */
    public function getAttachment(): ?Model
    {
        return $this->note;
    }
}
