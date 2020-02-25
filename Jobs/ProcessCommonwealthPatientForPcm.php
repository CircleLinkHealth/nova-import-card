<?php

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Decorators\CareTeamFromAthena;
use CircleLinkHealth\Eligibility\Decorators\DemographicsFromAthena;
use CircleLinkHealth\Eligibility\Decorators\InsuranceFromAthena;
use CircleLinkHealth\Eligibility\Decorators\PcmChargeableServices;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
     * @param DemographicsFromAthena $addDemographicsFromAthenaToEligibilityJob
     * @param CareTeamFromAthena $addCareTeamFromAthenaToEligibilityJob
     * @param InsuranceFromAthena $addInsuranceFromAthenaToEligibilityJob
     * @param PcmChargeableServices $addPcmChargeableServices
     *
     * @return void
     * @throws \Exception
     */
    public function handle(
        DemographicsFromAthena $addDemographicsFromAthenaToEligibilityJob,
        CareTeamFromAthena $addCareTeamFromAthenaToEligibilityJob,
        InsuranceFromAthena $addInsuranceFromAthenaToEligibilityJob,
        PcmChargeableServices $addPcmChargeableServices
    ) {
        $addPcmChargeableServices->decorate(
            $addInsuranceFromAthenaToEligibilityJob->decorate(
                $addDemographicsFromAthenaToEligibilityJob->decorate(
                    $addCareTeamFromAthenaToEligibilityJob->decorate(
                        $this->eligibilityJob
                    )
                )
            )
        )->save();
    }
}
