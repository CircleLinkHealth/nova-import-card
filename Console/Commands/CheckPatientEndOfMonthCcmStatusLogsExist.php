<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console\Commands;

use CircleLinkHealth\CcmBilling\Jobs\CheckPatientEndOfMonthCcmStatusLogsExistForMonth as Job;

class CheckPatientEndOfMonthCcmStatusLogsExist extends CommandForSpecificMonth
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if end of month ccm status logs have been created. If not, attempt again.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'billing:check-end-of-month-ccm-status-logs {month?}';

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
