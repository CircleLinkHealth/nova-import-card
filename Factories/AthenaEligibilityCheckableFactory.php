<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Factories;

use CircleLinkHealth\Eligibility\Exceptions\CcdaWasNotFetchedFromAthenaApi;
use CircleLinkHealth\Eligibility\Entities\TargetPatient;
use CircleLinkHealth\Eligibility\Checkables\AthenaCheckable;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use CircleLinkHealth\Eligibility\Contracts\Checkable;
use CircleLinkHealth\Eligibility\Tasks\CreateCcdaFromAthenaApi;

/**
 * This class encapsulates the logic for creating an Checkable for AthenaApiImplementation patients.
 *
 * @see \CircleLinkHealth\Eligibility\Contracts\Checkable
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
     * @throws Exception
     *
     * @return AthenaCheckable
     */
    public function makeAthenaEligibilityCheckable(TargetPatient $targetPatient): AthenaCheckable
    {
        $ccda = $targetPatient->ccda;

        if ( ! $ccda) {
            try {
                $ccda = app(CreateCcdaFromAthenaApi::class)->handle($targetPatient);
            } catch (CcdaWasNotFetchedFromAthenaApi $e) {
                $targetPatient->setStatusFromException($e);
                $targetPatient->save();
            }
        }

        return new AthenaCheckable($ccda, $targetPatient->practice, $targetPatient->batch, $targetPatient);
    }
}
