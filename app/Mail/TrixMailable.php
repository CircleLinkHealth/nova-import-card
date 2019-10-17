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

    /**
     * Create a new message instance.
     *
     * @param mixed $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('patient.patient-email')
            ->with([
                'content' => $this->content,
            ])
            ->from('no-replyg@circlelinkhealth.com', 'CircleLink Health')
            ->subject('You have received a message from CircleLink Health');
    }
}
