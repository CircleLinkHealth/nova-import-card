<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use CircleLinkHealth\CcmBilling\Entities\AttestedProblem;
use CircleLinkHealth\Core\Jobs\EncryptedLaravelJob as Job;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;

class MigrateAttestationDataToNewBillingColumns extends Job implements ShouldBeEncrypted
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
