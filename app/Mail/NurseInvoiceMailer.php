<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NurseInvoiceMailer extends Mailable
{
    use Queueable, SerializesModels;

    protected $link;
    protected $recipientName;
    protected $month;

    public function __construct($recipientName, $link, $month)
    {
        $this->link          = $link;
        $this->recipientName = $recipientName;
        $this->month         = $month;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.nurseInvoice')
                    ->with(['name' => $this->recipientName])
                    ->subject("$this->month Time and Fees Report")
                    ->attach(storage_path("download/$this->link"), [
                        'as'   => 'invoice.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
