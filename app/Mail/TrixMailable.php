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

    protected $mailAttachments;

    protected $patient;

    /**
     * Create a new message instance.
     *
     * @param mixed $content
     * @param array $mailAttachments
     * @param mixed $patient
     */
    public function __construct($patient, $content, $mailAttachments = [])
    {
        $this->patient         = $patient;
        $this->content         = $content;
        $this->mailAttachments = $mailAttachments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->view('patient.patient-email')
            ->with([
                'content' => $this->content,
            ])
            ->from('no-replyg@circlelinkhealth.com', 'CircleLink Health')
            ->subject('You have received a message from CircleLink Health');

        if ( ! empty($this->mailAttachments)) {
            foreach ($this->mailAttachments as $attachment) {
                $email->attachFromStorageDisk('cloud', $attachment['path']);
            }
        }

        return $email;
    }
}
