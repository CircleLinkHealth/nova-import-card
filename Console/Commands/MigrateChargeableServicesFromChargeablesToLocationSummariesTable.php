<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console\Commands;

use CircleLinkHealth\CcmBilling\Jobs\MigrateChargeableServicesFromChargeablesToLocationSummariesTable as Job;

class MigrateChargeableServicesFromChargeablesToLocationSummariesTable extends CommandForSpecificMonth
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Billing-revamp: Get CS from chargeables for each Practice, and migrate to chargeable_location_monthly_summaries.';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'billing:migrate-all-location-services {month?}';

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
