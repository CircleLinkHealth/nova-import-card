<?php

namespace App\Mail;

use App\Billing\NurseMonthlyBillGenerator;
use App\MailLog;
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
    protected $reportLink;

    protected $rangeStart;
    protected $rangeEnd;

    public function __construct(Nurse $nurse, Carbon $start, Carbon $end, $withVariable = false)
    {

        $this->nurse = $nurse;
        $this->reportLink = (new NurseMonthlyBillGenerator($nurse, $start, $end, $withVariable))->generatePdf(true);

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

        $month = Carbon::now()->format('F');

        MailLog::create([
            'sender_email' => 'no-reply@circlelinkhealth.com',
            'receiver_email' => $this->nurse->user->email,
            'body' => '',
            'subject' => "$month Time and Fees Earned Report",
            'type' => 'invoice',
            'sender_cpm_id' => 1752,
            'receiver_cpm_id' => $this->nurse->user->id,
            'created_at' => Carbon::now()->toDateTimeString(),
            'note_id' => null
        ]);


        return $this->view('emails.nurseInvoice')
            ->with(['name' => $this->nurse->user->fullName])
            ->subject("$month Time and Fees Earned Report")
            ->attach($this->reportLink, [
                'as' => 'invoice.pdf',
                'mime' => 'application/pdf',
            ])->attach();

    }
}
