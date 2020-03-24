<?php

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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var EligibilityJob
     */
    public $eligibilityJob;
    
    /**
     * Create a new job instance.
     *
     * @param EligibilityJob $eligibilityJob
     */
    public function __construct(EligibilityJob $eligibilityJob)
    {
        $this->eligibilityJob = $eligibilityJob;
    }
    
    /**
     * Execute the job.
     *
     * @param PcmChargeableServices $addPcmChargeableServices
     *
     * @return void
     */
    public function handle(
        PcmChargeableServices $addPcmChargeableServices
    ) {
        $addPcmChargeableServices->decorate($this->eligibilityJob)->save();
    }
}
