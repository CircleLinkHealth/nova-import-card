<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\EndOfMonthCcmStatusLog;
use MichaelLedin\LaravelJob\Job;

class CheckPatientEndOfMonthCcmStatusLogsExistForMonth extends Job
{
    protected Carbon $month;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Carbon $month = null)
    {
        $this->month = $month ?? Carbon::now()->startOfMonth()->startOfDay();
    }

    public static function fromParameters(string ...$parameters)
    {
        $date = isset($parameters[0]) ? Carbon::parse($parameters[0]) : null;
        return new static($date);
    }

    public function getMonth(): Carbon
    {
        return $this->month;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( ! EndOfMonthCcmStatusLog::logsExistForMonth($this->getMonth())) {
            $readableMonth = $this->getMonth()->format('M, Y');
            sendSlackMessage('#cpm_general_alerts', "End of month Ccm status logs do not exist for $readableMonth. Re-attempting log creation.");
            GenerateEndOfMonthCcmStatusLogs::dispatch($this->getMonth());
        }
    }
}
