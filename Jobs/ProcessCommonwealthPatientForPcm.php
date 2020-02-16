<?php

namespace CircleLinkHealth\Eligibility\Jobs;

use CircleLinkHealth\Eligibility\Decorators\AddCareTeamFromAthenaToEligibilityJob;
use CircleLinkHealth\Eligibility\Decorators\AddDemographicsFromAthenaToEligibilityJob;
use CircleLinkHealth\Eligibility\Decorators\AddInsuranceFromAthenaToEligibilityJob;
use CircleLinkHealth\Eligibility\Decorators\AddPcmChargeableServices;
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
     * @param AddDemographicsFromAthenaToEligibilityJob $addDemographicsFromAthenaToEligibilityJob
     * @param AddCareTeamFromAthenaToEligibilityJob $addCareTeamFromAthenaToEligibilityJob
     * @param AddInsuranceFromAthenaToEligibilityJob $addInsuranceFromAthenaToEligibilityJob
     * @param AddPcmChargeableServices $addPcmChargeableServices
     *
     * @return void
     * @throws \Exception
     */
    public function handle(
        AddDemographicsFromAthenaToEligibilityJob $addDemographicsFromAthenaToEligibilityJob,
        AddCareTeamFromAthenaToEligibilityJob $addCareTeamFromAthenaToEligibilityJob,
        AddInsuranceFromAthenaToEligibilityJob $addInsuranceFromAthenaToEligibilityJob,
        AddPcmChargeableServices $addPcmChargeableServices
    ) {
        $addPcmChargeableServices->addPcm(
            $addInsuranceFromAthenaToEligibilityJob->addInsurancesFromAthena(
                $addDemographicsFromAthenaToEligibilityJob->addDemographicsFromAthena(
                    $addCareTeamFromAthenaToEligibilityJob->addCareTeamFromAthena(
                        $this->eligibilityJob,
                        $this->eligibilityJob->targetPatient,
                        $this->eligibilityJob->targetPatient->ccda
                    ),
                    $this->eligibilityJob->targetPatient,
                    $this->eligibilityJob->targetPatient->ccda
                ),
                $this->eligibilityJob->targetPatient,
                $this->eligibilityJob->targetPatient->ccda
            )
        )->save();
    }
}
