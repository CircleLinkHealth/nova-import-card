<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Mail;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PracticeInvoice extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The attachment to the Mailable.
     *
     * For an example @see: PracticeInvoiceController, method send
     */
    protected $invoiceURL;

    /**
     * The link passed to the view.
     *
     * For an example @see: PracticeInvoiceController, method send
     */
    protected $patientReportURL;

    /**
     * @var User
     */
    protected $recipient;

    /**
     * Create a new message instance.
     *
     * @param $patientReportURL
     * @param $invoiceURL
     *
     * @throws \Exception
     */
    public function __construct($patientReportURL, $invoiceURL)
    {
        $this->patientReportURL = $patientReportURL;
        $this->invoiceURL       = $invoiceURL;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('billing.practice.mail')
            ->with([
                'patientReportURL' => $this->patientReportURL,
                'invoiceURL'       => $this->invoiceURL,
            ])
            ->from('billing@circlelinkhealth.com', 'CircleLink Health')
            ->subject('Your Invoice and Billing Report from CircleLink');
    }
}
