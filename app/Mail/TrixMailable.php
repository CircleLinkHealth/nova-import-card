<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Mail;

use CircleLinkHealth\Customer\Entities\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrixMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected $content;

    protected $emailSubject;

    protected $mailAttachments;

    protected $patient;

    /**
     * Create a new message instance.
     *
     * @param mixed  $patient
     * @param mixed  $content
     * @param array  $mailAttachments
     * @param string $emailSubject
     */
    public function __construct(
        $patient,
        $content,
        $mailAttachments = [],
        $emailSubject
    ) {
        $this->patient         = $patient;
        $this->content         = $content;
        $this->mailAttachments = $mailAttachments;
        $this->emailSubject    = ! empty($emailSubject) ? $emailSubject : 'You have received a message from your personalized Care Coach';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //get media to embed images
        $media = [];
        foreach ($this->mailAttachments as $attachment) {
            $media[] = Media::where('collection_name', 'patient-email-attachments')
                ->where('model_id', $this->patient->id)
                ->whereIn('model_type', [\App\User::class, 'CircleLinkHealth\Customer\Entities\User'])
                ->where('mime_type', 'like', '%'.'image'.'%')
                ->find($attachment['media_id']);
        }

        $email = $this->view('patient.patient-email')
            ->with([
                'practiceName' => $this->patient->getPrimaryPracticeName(),
                'content'      => $this->content,
                'attachments'  => collect($media)->filter(),
            ])
            ->from(config('mail.from.address'), 'Care Coaching Team')
            ->subject($this->emailSubject);

        //attach media
        if ( ! empty($this->mailAttachments)) {
            foreach ($this->mailAttachments as $attachment) {
                $email->attachFromStorageDisk('cloud', $attachment['path']);
            }
        }

        return $email;
    }
}
