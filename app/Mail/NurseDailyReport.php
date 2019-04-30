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
     * The date for which the report is being generated.
     *
     * @var Carbon
     */
    protected $date;

    /**
     * @var User
     */
    protected $nurse;

    private static $nursesForNewReport = [
        11321,
        8151,
        1920,
    ];

    /**
     * Create a new message instance.
     *
     * @param User  $nurse
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
        $view = 'emails.nurseDailyReportToDeprecate';

        //only these 3 nurses get new report
        if (in_array($this->nurse->id, static::$nursesForNewReport)) {
            $view = 'emails.nurseDailyReport';
        }

        return $this->view($view)
            ->with($this->data)
            ->with(['date' => $this->date])
            ->to($this->nurse->email)
            ->from('notifications@careplanmanager.com', 'CircleLink Health')
            ->subject("CircleLink Daily Time Report for ({$this->date->format('m/d/Y')})");
    }
}
