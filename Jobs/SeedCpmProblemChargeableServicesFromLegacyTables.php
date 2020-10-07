<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use CircleLinkHealth\Customer\Entities\Practice;
use MichaelLedin\LaravelJob\Job;

class SeedCpmProblemChargeableServicesFromLegacyTables extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Practice::each(fn (Practice $practice) => SeedPracticeCpmProblemChargeableServicesFromLegacyTables::dispatch($practice->id));
    }
}
