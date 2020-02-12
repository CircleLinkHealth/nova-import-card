<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Decorators;

use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class AddCareTeamFromAthenaToEligibilityJob
{
    /**
     * @var AthenaApiImplementation
     */
    protected $athenaApiImplementation;
    
    public function __construct(AthenaApiImplementation $athenaApiImplementation)
    {
        $this->athenaApiImplementation = $athenaApiImplementation;
    }
    
    /**
     * @param EligibilityJob $eligibilityJob
     * @param TargetPatient $targetPatient
     * @param Ccda $ccda
     *
     * @return EligibilityJob
     * @throws \Exception
     */
    public function addCareTeamFromAthena(EligibilityJob $eligibilityJob) :EligibilityJob
    {
        $careTeam = $this->athenaApiImplementation->getCareTeam(
            $eligibilityJob->targetPatient->ehr_patient_id,
            $eligibilityJob->targetPatient->ehr_practice_id,
            $eligibilityJob->targetPatient->ehr_department_id
        );
        
        if (is_array($careTeam)) {
            $data                 = $eligibilityJob->data;
            $data['care_team']    = $careTeam;
            $eligibilityJob->data = $data;
            $eligibilityJob->save();
        }
        
        return $eligibilityJob;
    }
}
