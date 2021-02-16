<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Decorators;

use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Contracts\MedicalRecordDecorator;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use CircleLinkHealth\SharedModels\Entities\EligibilityJob;

class CareTeamFromAthena implements MedicalRecordDecorator
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
     * @param \CircleLinkHealth\SharedModels\Entities\TargetPatient $targetPatient
     * @param Ccda                                                  $ccda
     *
     * @throws \Exception
     */
    public function decorate(EligibilityJob $eligibilityJob): EligibilityJob
    {
        $eligibilityJob->loadMissing('targetPatient.ccda');

        if (array_key_exists('care_team', $eligibilityJob->data) && ! empty($eligibilityJob->data['care_team'])) {
            return $this->addCareTeamFromEligibilityJob($eligibilityJob);
        }

        return $this->addCareTeamFromApi($eligibilityJob);
    }

    /**
     * @throws \Exception
     */
    private function addCareTeamFromApi(EligibilityJob &$eligibilityJob): EligibilityJob
    {
        $careTeam = $this->athenaApiImplementation->getCareTeam(
            $eligibilityJob->targetPatient->ehr_patient_id,
            $eligibilityJob->targetPatient->ehr_practice_id,
            $eligibilityJob->targetPatient->ehr_department_id
        );

        if (is_array($careTeam)) {
            $this->fillProvider($eligibilityJob, $eligibilityJob->targetPatient->ccda, $careTeam);
        }

        return $eligibilityJob;
    }

    private function addCareTeamFromEligibilityJob(EligibilityJob &$eligibilityJob): EligibilityJob
    {
        $careTeam = $eligibilityJob->data['care_team'];

        if (is_array($careTeam) && empty($eligibilityJob->data['referring_provider_name'])) {
            $this->fillProvider($eligibilityJob, $eligibilityJob->targetPatient->ccda, $careTeam);
        }

        return $eligibilityJob;
    }

    private function fillProvider(EligibilityJob &$eligibilityJob, Ccda $ccda, array &$careTeam)
    {
        $data              = $eligibilityJob->data;
        $data['care_team'] = $careTeam;

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
