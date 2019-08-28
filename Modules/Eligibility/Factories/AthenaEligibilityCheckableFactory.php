<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Contracts\EligibilityCheckable;
use App\TargetPatient;
use CircleLinkHealth\Eligibility\Checkables\AthenaPatient;
use CircleLinkHealth\Eligibility\Commands\CreateCcdaFromAthenaApi;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;

/**
 * This class encapsulates the logic for creating an EligibilityCheckable for AthenaApiImplementation patients.
 *
 * @see EligibilityCheckable
 *
 * Class AthenaEligibilityCheckableFactory
 */
class AthenaEligibilityCheckableFactory
{
    /**
     * @var AthenaApiImplementation
     */
    protected $athenaApi;

    public function __construct(AthenaApiImplementation $athenaApi)
    {
        $this->athenaApi = $athenaApi;
    }

    /**
     * @param TargetPatient $targetPatient
     *
     * @return AthenaPatient
     */
    public function makeAthenaPatientFromApi(TargetPatient $targetPatient): AthenaPatient
    {
        $ccda = $targetPatient->ccda;

        if ( ! $ccda) {
            $ccda = new CreateCcdaFromAthenaApi($targetPatient);
        }

        return new AthenaPatient($ccda, $targetPatient->practice, $targetPatient->batch, $targetPatient);
    }
}
