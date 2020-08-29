<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Console;

use CircleLinkHealth\CcmBilling\Jobs\MigrateChargeableServicesFromChargeablesToLocationSummariesTable as Job;
use Illuminate\Console\Command;

class MigrateChargeableServicesFromChargeablesToLocationSummariesTable extends Command
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
    protected $name = 'billing:migrate-location-services';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //pass in month
        Job::dispatch();
    }
}
