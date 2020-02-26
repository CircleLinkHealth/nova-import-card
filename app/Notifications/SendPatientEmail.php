<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications;

use App\Mail\TrixMailable;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SendPatientEmail extends Notification
{
    use Queueable;

    protected $attachments;

    protected $content;

    protected $emailSubject;

    protected $noteId;

    protected $patient;

    protected $senderId;

    /**
     * Create a new notification instance.
     *
     * @param $senderId
     * @param mixed      $content
     * @param mixed      $attachments
     * @param mixed|null $noteId
     * @param mixed      $emailSubject
     */
    public function __construct(User $patient, $senderId, string $content, $attachments, $noteId = null, $emailSubject)
    {
        $this->senderId     = $senderId;
        $this->patient      = $patient;
        $this->content      = $content;
        $this->attachments  = $attachments;
        $this->noteId       = $noteId;
        $this->emailSubject = $emailSubject;
    }

    public function __destruct()
    {
        if ( ! empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                if (file_exists($attachment['path'])) {
                    unlink($attachment['path']);
                }
            }
        }
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
        $toArray = [
            'recipient_email' => $notifiable->email,
            'email_content'   => $this->content,
            'email_subject'   => $this->emailSubject,
            'sender_id'       => $this->senderId,
        ];

        if ( ! empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                $toArray['attachments'][] = ['media_id' => $attachment['media_id']];
            }
        }

        if ($this->noteId) {
            $toArray['note_id'] = $this->noteId;
        }

        return $toArray;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return TrixMailable
     */
    public function toMail($notifiable)
    {
        return (new TrixMailable($this->patient, $this->content, $this->attachments, $this->emailSubject))
            ->to($notifiable->email);
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
        return ['mail', 'database'];
    }
}
