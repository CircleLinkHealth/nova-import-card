<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NurseDailyReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The data passed to the view
     *
     * For an example @see: EmailRNDailyReport, method handle
     * @var array
     */
    protected $data;

    /**
     * @var User
     */
    protected $nurse;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $nurse, array $data)
    {
        $this->nurse = $nurse;

        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.nurseDailyReport')
            ->with($this->data)
            ->to($this->nurse->email)
            ->from('notifications@careplanmanager.com', 'CircleLink Health')
            ->subject('CircleLink Daily Time Report');
    }
}
