<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Contracts\HasAttachment;
use App\Note;
use App\ValueObjects\SimpleNotification;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoteForwarded extends Notification implements ShouldQueue, HasAttachment
{
    use Queueable;
    public $attachment;
    public $channels = ['database'];

    /**
     * @var Note
     */
    public $note;

    public $pathToPdf;

    /**
     * Create a new notification instance.
     *
     * @param Note  $note
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
     * Returns an Eloquent model.
     *
     * @return Model|null
     */
    public function getAttachment(): ?Model
    {
        return $this->note;
    }

    /**
     * Get the body of a DM.
     *
     * @return string
     */
    public function getDMBody()
    {
        $link = $this->note->link();

        $message  = 'Please find attached a forwarded note regarding one of your patients';
        $lastLine = PHP_EOL.PHP_EOL."The web version of the note can be found at $link";

        return $this->getBody($message, $lastLine);
    }

    /**
     * Get the body of the email.
     *
     * @return string
     */
    public function getEmailBody()
    {
        return $this->getBody('Please click below button to see a forwarded note regarding one of your patients');
    }

    /**
     * Get the mail's subject.
     *
     * @return string
     */
    public function getSubject()
    {
        if ($this->note->isTCM) {
            return 'Urgent Patient Note from '.$this->note->patient->saasAccountName();
        }

        return 'You have been forwarded a note from CarePlanManager';
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
            'channels'    => $this->channels,
            'sender_id'   => auth()->id(),
            'sender_type' => auth()->check()
                ? User::class
                : null,
            'sender_email'   => optional(auth()->user())->email,
            'receiver_type'  => get_class($notifiable),
            'receiver_id'    => $notifiable->id,
            'receiver_email' => $notifiable->email,
            'email_body'     => $this->getEmailBody(),
            'dm_body'        => $this->getDMBody(),
            'link'           => $this->note->link(),
            'subject'        => $this->getSubject(),
            'note_id'        => $this->note->id,
            'pathToPdf'      => $this->pathToPdf,
        ];
    }

    /**
     * Get a pdf representation of the note to send via DM.
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

        return (new SimpleNotification())
            ->setBody($this->getDMBody())
            ->setSubject($this->getSubject())
            ->setFilePath($this->toPdf());
    }

    /**
     * Get a pdf representation of the note to send via Fax.
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
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $saasAccountName     = $notifiable->saasAccountName();
        $slugSaasAccountName = strtolower(str_slug($saasAccountName, ''));

        $mail = (new MailMessage())
            ->view(
                'vendor.notifications.email',
                [
                    'greeting'        => $this->getEmailBody(),
                    'actionText'      => 'View Note',
                    'actionUrl'       => $this->note->link(),
                    'introLines'      => [],
                    'outroLines'      => [],
                    'level'           => '',
                    'saasAccountName' => $saasAccountName,
                ]
            )
            ->from("no-reply@${slugSaasAccountName}.com", $saasAccountName)
            ->subject($this->getSubject());

        if ('circlelink-health' == $notifiable->saasAccount->slug) {
            return $mail->bcc(
                [
                    'raph@circlelinkhealth.com',
                    'abigail@circlelinkhealth.com',
                    'sheller@circlelinkhealth.com',
                ]
            );
        }

        return $mail;
    }

    /**
     * Get a pdf representation of the note.
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
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return $this->channels;
    }

    /**
     * Factory for message body.
     *
     * @param $greeting
     * @param mixed $lastLine
     *
     * @return string
     */
    private function getBody($greeting, $lastLine = '')
    {
        $message = $greeting.', created on '
                   .$this->note->performed_at->toFormattedDateString();

        if ($this->note->author) {
            $message .= ' by '.$this->note->author->getFullName().'.';
        }

        if (auth()->check()) {
            $message .= PHP_EOL.PHP_EOL.'The note was forwarded to you by '.auth()->user()->getFullName().'.';
        }

        $message .= $lastLine;

        return $message;
    }
}
