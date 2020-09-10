<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console\Commands;

use CircleLinkHealth\CcmBilling\Jobs\GenerateServiceSummariesForAllPracticeLocations as Job;

class GenerateServiceSummariesForAllPracticeLocations extends CommandForSpecificMonth
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CS summaries for all practice locations based on previous month.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'billing:generate-locations-summaries-for-month {month}';

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
