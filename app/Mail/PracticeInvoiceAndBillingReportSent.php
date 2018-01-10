<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PracticeInvoiceAndBillingReportSent extends Mailable
{
    use Queueable, SerializesModels;
    private $invoiceLink;
    private $invoicePath;

    /**
     * Create a new message instance.
     *
     * @param $invoiceLink
     * @param $invoicePath
     */
    public function __construct($invoiceLink, $invoicePath)
    {
        $this->invoiceLink = $invoiceLink;
        $this->invoicePath = $invoicePath;
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
                    ->attach($this->invoicePath);
    }
}
