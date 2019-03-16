<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Adapters\EligibilityCheck;

use App\EligibilityBatch;
use App\EligibilityJob;
use App\Services\WelcomeCallListGenerator;
use App\ValueObjects\Athena\ProblemsAndInsurances;

class AthenaAPIAdapter
{
    private $eligibilityBatch;
    private $eligibilityJob;
    private $eligiblePatientList;
    private $problemsAndInsurances;
    
    public function __construct(
        ProblemsAndInsurances $problemsAndInsurances,
        EligibilityJob $job = null,
        EligibilityBatch $batch = null
    ) {
        $this->problemsAndInsurances = $problemsAndInsurances;
        $this->eligibilityJob        = $job;
        $this->eligibilityBatch      = $batch;
    }
    
    public function getEligibilityJob()
    {
        return $this->eligibilityJob;
    }
    
    /**
     * @return mixed
     */
    public function getEligiblePatientList()
    {
        return $this->eligiblePatientList;
    }
    
    public function isEligible()
    {
        $patientList = collect();
        
        $patient = collect(
            [
                'problems'   => $this->problemsAndInsurances->getProblemCodes(),
                'insurances' => $this->problemsAndInsurances->getInsurancesForEligibilityCheck(),
            ]
        );
        
        $patientList->push($patient);
        
        $check = new WelcomeCallListGenerator(
            $patientList,
            false,
            true,
            true,
            false,
            null,
            null,
            null,
            $this->eligibilityBatch,
            $this->eligibilityJob
        );
        
        $this->eligibilityJob      = $check->getEligibilityJob();
        $this->eligiblePatientList = $check->getPatientList();
        
        return $this->eligiblePatientList->count() > 0;
    }
}
