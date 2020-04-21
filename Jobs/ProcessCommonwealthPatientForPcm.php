<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Decorators\PcmChargeableServices;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCommonwealthPatientForPcm implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var EligibilityJob
     */
    public $eligibilityJob;

    /**
     * Create a new job instance.
     */
    public function __construct(EligibilityJob $eligibilityJob)
    {
        $this->eligibilityJob = $eligibilityJob;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        PcmChargeableServices $addPcmChargeableServices
    ) {
        $addPcmChargeableServices->decorate($this->eligibilityJob)->save();
    }
}
