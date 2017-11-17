<?php

namespace App\Notifications;

use App\Note;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoteForwarded extends Notification
{
    use Queueable;

    public $note;
    public $channels = ['database'];
    public $pathToPdf;


    /**
     * Create a new notification instance.
     *
     * @param Note $note
     * @param array $channels
     */
    public function __construct(
        Note $note,
        $channels = ['mail']
    ) {
        $this->note = $note;

        $this->channels = array_merge($this->channels, $channels);
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
     * Get a pdf representation of the note to send via Fax
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

        return $this->toPdf();
    }

    /**
     * Get a pdf representation of the note to send via DM
     *
     * @param $notifiable
     *
     * @return bool|string
     */
    public function toDirectMail($notifiable)
    {
        if ( ! $notifiable || ! $notifiable->emr_direct_address) {
            return false;
        }

        return $this->toPdf();
    }

    /**
     * Get a pdf representation of the note
     *
     * @return string
     */
    public function toPdf()
    {
        if ( ! file_exists($this->pathToPdf)) {
            $this->pathToPdf = $this->note->toPdf();
        }

        return $this->pathToPdf;
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
            'channels'        => $this->channels,
            'sender_email'    => auth()->user()->email,
            'receiver_email'  => $notifiable->email,
            'body'            => $this->getBody(),
            'subject'         => $this->getSubject(),
            'sender_cpm_id'   => auth()->user()->id,
            'receiver_cpm_id' => $notifiable->id,
            'note_id'         => $this->note->id,
            'pathToPdf'       => $this->pathToPdf,
        ];
    }
}
