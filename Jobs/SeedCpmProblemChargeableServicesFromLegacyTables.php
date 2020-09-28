<?php

namespace CircleLinkHealth\CcmBilling\Jobs;

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
        //
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
