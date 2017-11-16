<?php

namespace App\Mail;

use App\Practice;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SalesPracticeReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    protected $practice;


    /**
     * The data passed to the view
     *
     * For an example @see: SalesReportsController, method makePracticeReport
     * @var array
     */
    protected $data;

    protected $recipientEmail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Practice $practice, array $data, $recipientEmail)
    {
        $this->practice = $practice;
        $this->data = $data;
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
            ->subject($this->practice->display_name . '\'s CCM Weekly Summary');
    }
}
