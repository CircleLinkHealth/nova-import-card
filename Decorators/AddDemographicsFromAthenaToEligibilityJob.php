<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Decorators;

use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class AddDemographicsFromAthenaToEligibilityJob
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
    public function addDemographicsFromAthena(
        EligibilityJob $eligibilityJob,
        TargetPatient $targetPatient,
        Ccda $ccda
    ): EligibilityJob {
        $data                         = $eligibilityJob->data;
        
        if ( ! array_key_exists('patient_demographics', $eligibilityJob->data)) {
            $demographics = $this->athenaApiImplementation->getDemographics(
                $targetPatient->ehr_patient_id,
                $targetPatient->ehr_practice_id
            );
            
            if (is_array($demographics) && array_key_exists(0, $demographics)) {
                $data['patient_demographics'] = $demographics[0];
            }
        }
        
        if ( ! array_key_exists('provider', $eligibilityJob->data) && array_key_exists('patient_demographics', $eligibilityJob->data)) {
            if ($provId = $data['patient_demographics']['primaryproviderid']) {
                $provider = $this->athenaApiImplementation->getProvider($targetPatient->ehr_practice_id, $provId);
            }
            
            if (is_array($provider) && array_key_exists(0, $provider)) {
                $data['referring_provider_name'] = $ccda->referring_provider_name = $provider[0]['displayname'];
                $data['provider']                = $provider[0];
                $ccda->save();
            }
            
            $eligibilityJob->data = $data;
            $eligibilityJob->save();
        }
        
        return $eligibilityJob;
    }
}
