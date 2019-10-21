<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrixMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected $content;

    protected $mailAttachments;

    /**
     * Create a new message instance.
     *
     * @param mixed $content
     * @param array $mailAttachments
     */
    public function __construct($content, $mailAttachments = [])
    {
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
                $email->attach($attachment['path']);
            }
        }

        return $email;
    }
}
