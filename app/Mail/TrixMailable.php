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

    protected $s3attachments;

    /**
     * Create a new message instance.
     *
     * @param mixed $content
     * @param mixed $s3attachments
     */
    public function __construct($content, $s3attachments = [])
    {
        $this->content       = $content;
        $this->s3attachments = $s3attachments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //todo:attach multiple?
        //check if attachments exist

        return $this->view('patient.patient-email')
            ->with([
                'content' => $this->content,
            ])
            ->from('no-replyg@circlelinkhealth.com', 'CircleLink Health')
            ->subject('You have received a message from CircleLink Health');

        //not working
//            ->attachFromStorageDisk('s3', $this->s3attachments[0]['path']);
    }
}
