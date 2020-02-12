<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Checkables;

use CircleLinkHealth\Eligibility\Adapters\AddCareTeamFromAthena;
use CircleLinkHealth\Eligibility\Adapters\AddInsurancesFromAthena;
use CircleLinkHealth\Eligibility\Adapters\CcdaToEligibilityJobAdapter;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecord;
use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Contracts\Checkable;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;

class AthenaCheckable implements Checkable
{
    /**
     * @var EligibilityBatch
     */
    protected $batch;
    /**
     * @var Ccda
     */
    protected $ccda;
    /**
     * @var Practice
     */
    protected $practice;
    /**
     * @var TargetPatient
     */
    protected $targetPatient;
    
    /**
     * @var EligibilityJob
     */
    private $eligibilityJob;
    
    public function __construct(Ccda $ccda, Practice $practice, EligibilityBatch $batch, TargetPatient $targetPatient)
    {
        $this->ccda          = $ccda;
        $this->practice      = $practice;
        $this->batch         = $batch;
        $this->targetPatient = $targetPatient;
    }
    
    /**
     * @throws \Exception
     *
     * @return EligibilityJob
     */
    public function createAndProcessEligibilityJobFromMedicalRecord(): EligibilityJob
    {
        if ( ! $this->eligibilityJob) {
            //We are "decorating" existing Ccda adapter to add insurance we will get from Athena API
            //In other words CcdaToEligibilityJobAdapter will extract data for eligibility from CCD. We will take that
            //result, add insurance from AthenaAPI to it and store it.
            $decoratedAdapter = new AddCareTeamFromAthena(
                new AddInsurancesFromAthena(
                    new CcdaToEligibilityJobAdapter($this->ccda, $this->practice, $this->batch),
                    $this->getTargetPatient(),
                    $this->ccda
                )
            );
            
            $this->eligibilityJob = $decoratedAdapter->adaptToEligibilityJob()
                                                     ->process()->getEligibilityJob();
        }
        
        return $this->eligibilityJob;
    }
    
    /**
     * @return EligibilityJob
     */
    public function getEligibilityJob(): EligibilityJob
    {
        return $this->eligibilityJob;
    }
    
    /**
     * @return \CircleLinkHealth\Eligibility\MedicalRecordImporter\\CircleLinkHealth\Eligibility\MedicalRecordImporter\Contracts\MedicalRecordForEligibilityCheck
     */
    public function getMedicalRecord(): MedicalRecord
    {
        return $this->ccda;
    }
    
    /**
     * This Model holds the data we need to make a request to an EHR API to get patient data.
     *
     * @return TargetPatient
     */
    public function getTargetPatient(): TargetPatient
    {
        return $this->targetPatient;
    }
}
