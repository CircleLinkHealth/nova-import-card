<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Decorators;

use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Contracts\MedicalRecordDecorator;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;

class DemographicsFromAthena implements MedicalRecordDecorator
{
    /**
     * @var AthenaApiImplementation
     */
    protected $athenaApiImplementation;

    public function __construct(AthenaApiImplementation $athenaApiImplementation)
    {
        $this->athenaApiImplementation = $athenaApiImplementation;
    }

    public function decorate(
        EligibilityJob $eligibilityJob
    ): EligibilityJob {
        $eligibilityJob->loadMissing('targetPatient.ccda');

        if ( ! array_key_exists('patient_demographics', $eligibilityJob->data)) {
            $this->getDemographicsFromApi($eligibilityJob);
        }

        if ( ! array_key_exists('provider', $eligibilityJob->data) && array_key_exists(
            'patient_demographics',
            $eligibilityJob->data
        )) {
            $this->getProviderFromApi($eligibilityJob);
        }

        if ($eligibilityJob->isDirty()) {
            $eligibilityJob->save();
        }

        return $eligibilityJob;
    }

    private function getDemographicsFromApi(EligibilityJob &$eligibilityJob)
    {
        if (is_null($eligibilityJob->targetPatient)) {
            return;
        }
        $demographics = $this->athenaApiImplementation->getDemographics(
            $eligibilityJob->targetPatient->ehr_patient_id,
            $eligibilityJob->targetPatient->ehr_practice_id
        );

        if (is_array($demographics) && array_key_exists(0, $demographics)) {
            $newData                         = $eligibilityJob->data;
            $newData['patient_demographics'] = $demographics[0];
            $eligibilityJob->data            = $newData;
        }
    }

    private function getProviderFromApi(EligibilityJob &$eligibilityJob)
    {
        $newData  = $eligibilityJob->data;
        $provider = null;

        if ($provId = $newData['patient_demographics']['primaryproviderid'] ?? null) {
            try {
                $provider = $this->athenaApiImplementation->getProvider(
                    $eligibilityJob->targetPatient->ehr_practice_id,
                    $provId
                );
            } catch (\Exception $exception) {
                \Log::error($exception->getMessage());
            }
        }

        if (is_array($provider) && array_key_exists(0, $provider)) {
            $newData['referring_provider_name'] = $eligibilityJob->targetPatient->ccda->referring_provider_name = $provider[0]['displayname'];
            $newData['provider']                = $provider[0];
            $eligibilityJob->targetPatient->ccda->save();
        }

        $eligibilityJob->data = $newData;
    }
}
