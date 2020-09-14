<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console\Commands;

use CircleLinkHealth\CcmBilling\Jobs\CheckPatientSummariesHaveBeenCreated as Job;

class CheckPatientSummariesHaveBeenCreated extends CommandForSpecificMonth
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if end of patient summaries have been created for a given month. If not, attempt again.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'billing:check-patient-summaries {month?}';

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
