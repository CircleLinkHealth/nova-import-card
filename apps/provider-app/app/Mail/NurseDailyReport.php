<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Mail;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NurseDailyReport extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The data passed to the view.
     *
     * For an example @see: EmailRNDailyReport, method handle
     *
     * @var array
     */
    protected $data;

    /**
     * The date for which the report is being generated.
     *
     * @var Carbon
     */
    protected $date;

    /**
     * @var User
     */
    protected $nurse;

    /**
     * Create a new message instance.
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
            ->with(['date' => $this->date])
            ->to($this->nurse->email)
            ->from('notifications@careplanmanager.com', 'CircleLink Health')
            ->subject("CLH Care Coach Daily Performance Report - ({$this->date->format('m/d/Y')})");
    }
}
