<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Mail;

use CircleLinkHealth\Customer\Entities\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NurseDailyReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The data passed to the view.
     *
     * For an example @see: EmailRNDailyReport, method handle
     *
     * @var array
     */
    protected $data;

    /**
     * @var User
     */
    protected $nurse;

    /**
     * The date for which the report is being generated
     *
     * @var Carbon
     */
    protected $date;
    
    /**
     * Create a new message instance.
     *
     * @param User $nurse
     * @param array $data
     */
    public function __construct(User $nurse, array $data, Carbon $date)
    {
        $this->nurse = $nurse;

        $this->data = $data;

        $this->date = $date;
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
            ->subject("CircleLink Daily Time Report ({$this->date->toDateString()})");
    }
}
