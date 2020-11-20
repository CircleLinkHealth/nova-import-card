<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use CircleLinkHealth\CcmBilling\Entities\AttestedProblem;
use MichaelLedin\LaravelJob\Job;

class MigrateAttestationDataToNewBillingColumns extends Job
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
        AttestedProblem::chunkIntoJobs(200, new MigrateAttestationDataChunk());
    }
}
