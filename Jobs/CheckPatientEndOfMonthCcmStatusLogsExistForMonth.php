<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\EndOfMonthCcmStatusLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckPatientEndOfMonthCcmStatusLogsExistForMonth implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
