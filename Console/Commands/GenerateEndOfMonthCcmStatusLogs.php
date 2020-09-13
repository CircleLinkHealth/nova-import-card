<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console\Commands;

use CircleLinkHealth\CcmBilling\Jobs\GenerateEndOfMonthCcmStatusLogs as Job;

class GenerateEndOfMonthCcmStatusLogs extends CommandForSpecificMonth
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate end of month ccm status logs for all patients in the system.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'billing:end-of-month-ccm-status-logs {month?}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Job::dispatch($this->month());
    }
}
