<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Decorators;

use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Contracts\MedicalRecordDecorator;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;

class CcdaFromAthena implements MedicalRecordDecorator
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

        if ($eligibilityJob->targetPatient && empty(optional($eligibilityJob->targetPatient->ccda)->json)) {
            $ccda    = AthenaEligibilityCheckableFactory::getCCDFromAthenaApi($eligibilityJob->targetPatient);
            $decoded = $ccda->bluebuttonJson(true);
        }

        return $eligibilityJob;
    }
}
