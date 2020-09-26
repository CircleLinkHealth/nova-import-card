<?php

namespace CircleLinkHealth\CcmBilling\Jobs;

use CircleLinkHealth\CcmBilling\Entities\AttestedProblem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
        //
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
