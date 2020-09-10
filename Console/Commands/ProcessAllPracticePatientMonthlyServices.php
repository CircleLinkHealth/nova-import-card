<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console\Commands;

use CircleLinkHealth\CcmBilling\Jobs\ProcessAllPracticePatientMonthlyServices as Job;

class ProcessAllPracticePatientMonthlyServices extends CommandForSpecificMonth
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all practice patients';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'billing:process-all-practice-patients {month?}';

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
