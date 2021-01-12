<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SMSReceived extends Mailable
{
    use Queueable;
    use SerializesModels;
    protected $request;

    /**
     * Create a new message instance.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.sms-received', [
            'request' => $this->request,
        ]);
    }
}
