<?php

namespace App\Mail;

use App\Billing\NurseMonthlyBillGenerator;
use App\Nurse;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NurseInvoiceMailer extends Mailable
{
    use Queueable, SerializesModels;

    protected $nurse;
    protected $reportData;
    protected $view;

    protected $rangeStart;
    protected $rangeEnd;


    public function __construct(Nurse $nurse, $withVariable, Carbon $start, Carbon $end)
    {

        $this->nurse = $nurse;
        $this->reportData = (new NurseMonthlyBillGenerator($nurse, $start, $end, $withVariable))->generatePdf();
        $this->reportData;
        $this->view = 'emails.nurseInvoice';

        $this->rangeStart = $start;
        $this->rangeEnd = $end;


    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        dd($this->reportData);

        return $this->view($this->view)
            ->from('no-reply@circlelinkhealth.com');
//            ->;

    }
}
