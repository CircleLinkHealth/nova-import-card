<?php

namespace App\Notifications;

use App\Channels\DirectMailChannel;
use App\Channels\FaxChannel;
use App\Location;
use App\Note;
use App\Traits\NotificationChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoteCreated extends Notification
{
    use Queueable;

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
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
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
        return (new MailMessage())
            ->view('vendor.notifications.email', [
                'greeting'   => $this->getBody(),
                'actionText' => 'View Note',
                'actionUrl'  => $this->note->link(),
                'introLines' => [],
                'outroLines' => [],
                'level'      => '',
            ])
            ->subject($this->getSubject())
            ->to($notifiable->email)
            ->bcc([
                'raph@circlelinkhealth.com',
                'chelsea@circlelinkhealth.com',
                'sheller@circlelinkhealth.com',
            ]);
    }



    /**
     * Get the body of the email
     *
     * @return string
     */
    public function getBody()
    {
        return 'Please click below button to see a forwarded note regarding one of your patients, created on '
               . $this->note->performed_at->toFormattedDateString()
               . ' by '
               . auth()->user()->fullName;
    }

    /**
     * Get the mail's subject
     *
     * @return string
     */
    public function getSubject()
    {
        if ($this->note->isTCM) {
            return 'Urgent Patient Note from CircleLink Health';
        }

        return 'You have been forwarded a note from CarePlanManager';
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
            'sender_email'    => auth()->user()->email,
            'receiver_email'  => $notifiable->email,
            'body'            => $this->getBody(),
            'subject'         => $this->getSubject(),
            'sender_cpm_id'   => auth()->user()->id,
            'receiver_cpm_id' => $notifiable->id,
            'note_id'         => $this->note->id,
        ];
    }
}
