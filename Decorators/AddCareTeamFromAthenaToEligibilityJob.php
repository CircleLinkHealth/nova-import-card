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
    public function addCareTeamFromAthena(EligibilityJob $eligibilityJob, TargetPatient $targetPatient, Ccda $ccda) :EligibilityJob
    {
        if (array_key_exists('care_team', $eligibilityJob->data) && ! empty($eligibilityJob->data['care_team'])) {
            $careTeam = $eligibilityJob->data['care_team'];
            
            if (is_array($careTeam) && empty($eligibilityJob->data['referring_provider_name'])) {
                $this->fillProvider($eligibilityJob, $ccda, $careTeam);
            }
            
            return $eligibilityJob;
        }
        
        $eligibilityJob->loadMissing('targetPatient');
        
        $careTeam = $this->athenaApiImplementation->getCareTeam(
            $targetPatient->ehr_patient_id,
            $targetPatient->ehr_practice_id,
            $targetPatient->ehr_department_id
        );
        
        if (is_array($careTeam)) {
            $this->fillProvider($eligibilityJob, $ccda, $careTeam);
        }
        
        return $eligibilityJob;
    }
    
    private function fillProvider(EligibilityJob &$eligibilityJob, Ccda $ccda, array &$careTeam)
    {
        $data                 = $eligibilityJob->data;
        $data['care_team']    = $careTeam;
    
    
        foreach ($careTeam['members'] as $member) {
            if (array_key_exists('name', $member)) {
                $providerName = $member['name'];
            
                $data['referring_provider_name'] = $ccda->referring_provider_name = $providerName;
                $ccda->save();
            
                break;
            }
        }
    
        $eligibilityJob->data = $data;
    
        $eligibilityJob->save();
    }
}
