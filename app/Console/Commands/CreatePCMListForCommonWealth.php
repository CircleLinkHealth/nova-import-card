<?php

namespace CircleLinkHealth\Eligibility\Console\Commands;

use CircleLinkHealth\Eligibility\Decorators\AddCareTeamFromAthenaToEligibilityJob;
use CircleLinkHealth\Eligibility\Decorators\AddDemographicsFromAthenaToEligibilityJob;
use CircleLinkHealth\Eligibility\Decorators\AddInsuranceFromAthenaToEligibilityJob;
use CircleLinkHealth\Eligibility\Decorators\AddPcmChargeableServices;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use Illuminate\Console\Command;

class CreatePCMListForCommonWealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pcm:common';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is the first time creating a PCM eligibility list for Commonwealth Pain Associates, PLLC';
    /**
     * @var AddDemographicsFromAthenaToEligibilityJob
     */
    protected $addDemographicsFromAthenaToEligibilityJob;
    /**
     * @var AddCareTeamFromAthenaToEligibilityJob
     */
    protected $addCareTeamFromAthenaToEligibilityJob;
    /**
     * @var AddInsuranceFromAthenaToEligibilityJob
     */
    protected $addInsuranceFromAthenaToEligibilityJob;
    
    const PRACTICE_ID = 232;
    /**
     * @var AddPcmChargeableServices
     */
    protected $addPcmChargeableServices;
    
    /**
     * Create a new command instance.
     *
     * @param AddDemographicsFromAthenaToEligibilityJob $addDemographicsFromAthenaToEligibilityJob
     * @param AddCareTeamFromAthenaToEligibilityJob $addCareTeamFromAthenaToEligibilityJob
     * @param AddInsuranceFromAthenaToEligibilityJob $addInsuranceFromAthenaToEligibilityJob
     * @param AddPcmChargeableServices $addPcmChargeableServices
     */
    public function __construct(
        AddDemographicsFromAthenaToEligibilityJob $addDemographicsFromAthenaToEligibilityJob,
        AddCareTeamFromAthenaToEligibilityJob $addCareTeamFromAthenaToEligibilityJob,
        AddInsuranceFromAthenaToEligibilityJob $addInsuranceFromAthenaToEligibilityJob,
        AddPcmChargeableServices $addPcmChargeableServices
    ) {
        parent::__construct();
        $this->addDemographicsFromAthenaToEligibilityJob = $addDemographicsFromAthenaToEligibilityJob;
        $this->addCareTeamFromAthenaToEligibilityJob     = $addCareTeamFromAthenaToEligibilityJob;
        $this->addInsuranceFromAthenaToEligibilityJob    = $addInsuranceFromAthenaToEligibilityJob;
        $this->addPcmChargeableServices    = $addPcmChargeableServices;
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        EligibilityJob::whereHas(
            'batch',
            function ($q) {
                $q->where('practice_id', self::PRACTICE_ID);
            }
        )->with(['eligibilityJob', 'targetPatient.ccda'])->chunkById(
            1,
            function ($jobs) {
                $jobs->each(
                    function ($job) {
                        $this->addPcmChargeableServices->decorate(
                            $this->addInsuranceFromAthenaToEligibilityJob->addInsurancesFromAthena(
                                $this->addDemographicsFromAthenaToEligibilityJob->addDemographicsFromAthena(
                                    $this->addCareTeamFromAthenaToEligibilityJob->addCareTeamFromAthena(
                                        $job,
                                        $job->targetPatient,
                                        $job->targetPatient->ccda
                                    ),
                                    $job->targetPatient,
                                    $job->targetPatient->ccda
                                ),
                                $job->targetPatient,
                                $job->targetPatient->ccda
                            )
                        )->save();
                    }
                );
            }
        );
    }
}
