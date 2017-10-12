<?php

namespace App\Mail;

use App\MailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NoteForwarded extends Mailable
{
    use Queueable, SerializesModels;
    protected $message;
    protected $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(MailLog $message, $noteUrl)
    {
        $this->message = $message;
        $this->url = $noteUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('vendor.notifications.email', [
            'greeting'   => $this->message->body,
            'actionText' => 'View Note',
            'actionUrl'  => $this->url,
            'introLines' => [],
            'outroLines' => [],
            'level'      => '',
        ])
            ->subject($this->message->subject)
            ->to($this->message->receiver_email)
            ->bcc(['raph@circlelinkhealth.com', 'chelsea@circlelinkhealth.com', 'sheller@circlelinkhealth.com']);
    }
}
