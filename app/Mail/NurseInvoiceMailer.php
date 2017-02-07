<?php

namespace App\Mail;

use App\Billing\NurseMonthlyBillGenerator;
use App\MailLog;
use App\Nurse;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NurseInvoiceMailer extends Mailable
{
    use Queueable, SerializesModels;

    protected $link;
    protected $recipient;
    protected $month;

    public function __construct($id, $link, $month)
    {

        $this->link = $link;
        $this->recipient = User::find($id);
        $this->month = $month;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        MailLog::create([
            'sender_email' => 'no-reply@circlelinkhealth.com',
            'receiver_email' => $this->recipient->email,
            'body' => '',
            'subject' => "$this->month Time and Fees Report",
            'type' => 'invoice',
            'sender_cpm_id' => 1752,
            'receiver_cpm_id' => $this->recipient->id,
            'created_at' => Carbon::now()->toDateTimeString(),
            'note_id' => null
        ]);


        return $this->view('emails.nurseInvoice')
            ->with(['name' => $this->recipient->fullName])
            ->subject("$this->month Time and Fees Report")
            ->attach(storage_path("download/$this->link"), [
                'as' => 'invoice.pdf',
                'mime' => 'application/pdf',
            ]);

    }
}
