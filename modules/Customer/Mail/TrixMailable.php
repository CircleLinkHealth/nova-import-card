<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Mail;

use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        User $patient,
        $content,
        $mailAttachments = [],
        $emailSubject
    ) {
        Log::debug("Patient email: Constructing mailable for email to patient:{$patient->id}");
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
        $media = Media::where('collection_name', 'patient-email-attachments')
            ->where('model_id', $this->patient->id)
            ->whereIn('model_type', [\App\User::class, \CircleLinkHealth\Customer\Entities\User::class])
            ->findMany(collect($this->mailAttachments)->pluck('media_id')->filter())->filter();

        $email = $this->view('patient.patient-email')
            ->with([
                'practiceName'  => $this->patient->getPrimaryPracticeName(),
                'practicePhone' => $this->getPracticePhone(),
                'content'       => $this->content,
                'attachments'   => $media->filter(fn ($m)   => Str::contains($m->mime_type, 'image')),
            ])
            ->from(config('mail.from.address'), 'Care Coaching Team')
            ->subject($this->emailSubject);

        foreach ($media as $attachment) {
            if (method_exists($attachment, 'getPath')) {
                $email->attachFromStorageDisk('media', $attachment->getPath());
            }
        }

        return $email;
    }

    private function getPracticePhone(): string
    {
        $phone = (new StringManipulation())->formatPhoneNumberWithNpaParenthesized($this->patient->primaryPractice->outgoing_phone_number);

        if (empty($phone)) {
            sendSlackMessage('#cpm_general_alerts', "URGENT! Practice {$this->patient->primaryPractice->id}, does not have an outgoing phone number!");

            return $phone;
        }

        return $phone;
    }
}
