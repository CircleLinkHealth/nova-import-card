<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Checkables;

use App\Adapters\EligibilityCheck\AddInsurancesFromAthena;
use App\Adapters\EligibilityCheck\CcdaToEligibilityJobAdapter;
use App\Contracts\Importer\MedicalRecord\MedicalRecord;
use App\EligibilityBatch;
use App\EligibilityJob;
use App\Models\MedicalRecords\Ccda;
use App\TargetPatient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\Contracts\Checkable;

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
     * @return \App\EligibilityJob
     */
    public function createAndProcessEligibilityJobFromMedicalRecord(): EligibilityJob
    {
        if ( ! $this->eligibilityJob) {
            //We are "decorating" existing Ccda adapter to add insurance we will get from Athena API
            //In other words CcdaToEligibilityJobAdapter will extract data for eligibility from CCD. We will take that
            //result, add insurance from AthenaAPI to it and store it.
            $decoratedAdapter = new AddInsurancesFromAthena(
                new CcdaToEligibilityJobAdapter($this->ccda, $this->practice, $this->batch),
                $this->getTargetPatient()
            );

            $this->eligibilityJob = $decoratedAdapter->adaptToEligibilityJob()
                ->process();
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
     * @return MedicalRecord
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
