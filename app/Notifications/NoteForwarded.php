<?php

namespace App\Notifications;

use App\Note;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoteForwarded extends Notification
{
    use Queueable;

    public $note;
    public $channels = ['database'];
    public $pathToPdf;
    public $attachment;

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
        $this->attachment = $this->note = $note;

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
        $saasAccountName     = $notifiable->saasAccountName();
        $slugSaasAccountName = strtolower(str_slug($saasAccountName, ''));

        $mail = (new MailMessage())
            ->view('vendor.notifications.email', [
                'greeting'        => $this->getBody(),
                'actionText'      => 'View Note',
                'actionUrl'       => $this->note->link(),
                'introLines'      => [],
                'outroLines'      => [],
                'level'           => '',
                'saasAccountName' => $saasAccountName,
            ])
            ->from("no-reply@$slugSaasAccountName.com", $saasAccountName)
            ->subject($this->getSubject());

        if ($notifiable->saasAccount->slug == 'circlelink-health') {
            return $mail->bcc([
                'raph@circlelinkhealth.com',
                'chelsea@circlelinkhealth.com',
                'sheller@circlelinkhealth.com',
            ]);
        }

        return $mail;
    }

    /**
     * Get the body of the email
     *
     * @return string
     */
    public function getBody()
    {
        $message = 'Please click below button to see a forwarded note regarding one of your patients, created on '
                   . $this->note->performed_at->toFormattedDateString();

        if (auth()->check()) {
            $message .= ' by ' . auth()->user()->getFullName();
        }

        return $message;
    }

    /**
     * Get the mail's subject
     *
     * @return string
     */
    public function getSubject()
    {
        if ($this->note->isTCM) {
            return 'Urgent Patient Note from ' . $this->note->patient->saasAccountName();
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
        if (! $notifiable || ! $notifiable->fax) {
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
        if (! file_exists($this->pathToPdf)) {
            $this->pathToPdf = $this->note->toPdf();
        }

        return $this->pathToPdf;
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
        if (! $notifiable || ! $notifiable->emr_direct_address) {
            return false;
        }

        return $this->toPdf();
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
            'channels' => $this->channels,

            'sender_id'    => auth()->id(),
            'sender_type'  => auth()->check()
                ? User::class
                : null,
            'sender_email' => optional(auth()->user())->email,

            'receiver_type'  => $notifiable->id,
            'receiver_id'    => get_class($notifiable),
            'receiver_email' => $notifiable->email,

            'body'    => $this->getBody(),
            'link'    => $this->note->link(),
            'subject' => $this->getSubject(),

            'note_id' => $this->note->id,

            'pathToPdf' => $this->pathToPdf,
        ];
    }

    /**
     * @return mixed
     */
    public function getAttachment()
    {
        return $this->attachment;
    }
}
