<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WeeklyPracticeReport extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * @var User
     */
    public $recipient;

    /**
     * The data passed to the view
     *
     * For an example @see: EmailWeeklyPracticeReport, method handle
     * @var array
     */
    public $data;

    /**
     * The subject passed to the view
     *
     * For an example @see: EmailWeeklyPracticeReport, method handle
     *
     */
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $recipient, array $data, $subject)
    {
        $this->recipient = $recipient;
        $this->data = $data;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('sales.by-practice.report')
            ->with($this->data)
            ->to($this->recipient->email)
            ->from('notifications@careplanmanager.com', 'CircleLink Health')
            ->subject($this->subject);
    }
}
