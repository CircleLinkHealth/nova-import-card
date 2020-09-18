<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Mail;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SalesPracticeReport extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The data passed to the view.
     *
     * For an example @see: SalesReportsController, method makePracticeReport
     *
     * @var array
     */
    protected $data;

    /**
     * @var User
     */
    protected $practice;

    protected $recipientEmail;

    /**
     * Create a new message instance.
     *
     * @param mixed $recipientEmail
     */
    public function __construct(Practice $practice, array $data, $recipientEmail)
    {
        $this->practice       = $practice;
        $this->data           = $data;
        $this->recipientEmail = $recipientEmail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('sales.by-practice.report')
            ->with(['data' => $this->data])
            ->to($this->recipientEmail)
            ->from('notifications@careplanmanager.com', 'CircleLink Health')
            ->subject($this->practice->display_name.'\'s CCM Weekly Summary');
    }
}
