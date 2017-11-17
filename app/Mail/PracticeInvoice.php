<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PracticeInvoice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    protected $recipient;


    /**
     * The link passed to the view
     *
     * For an example @see: PracticeInvoiceController, method send
     *
     */
    protected $invoiceLink;


    /**
     * The attachment to the Mailable
     *
     * For an example @see: PracticeInvoiceController, method send
     *
     */
    protected $filePath;



    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoiceLink, $filePath)
    {
        $this->invoiceLink = $invoiceLink;

        if (!file_exists($filePath)) {
            throw new \Exception("File does not exist");
        }
        $this->filePath = $filePath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('billing.practice.mail')
            ->with(['link' => $this->invoiceLink])
            ->from('billing@circlelinkhealth.com', 'CircleLink Health')
            ->subject('Your Invoice and Billing Report from CircleLink')
            ->attach($this->filePath);
    }
}
