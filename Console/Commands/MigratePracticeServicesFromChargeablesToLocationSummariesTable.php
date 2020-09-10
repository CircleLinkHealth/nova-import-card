<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console\Commands;

use CircleLinkHealth\CcmBilling\Jobs\MigratePracticeServicesFromChargeablesToLocationSummariesTable as Job;

class MigratePracticeServicesFromChargeablesToLocationSummariesTable extends CommandForSpecificMonth
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Billing-revamp: Get CS from chargeables for a single Practice, and migrate to chargeable_location_monthly_summaries.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'billing:migrate-practice-services {practiceId} {month?}';

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
        Job::dispatch($this->argument('practiceId'), $this->month());
    }
}
