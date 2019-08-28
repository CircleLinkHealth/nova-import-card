<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use App\EligibilityJob;
use App\TargetPatient;

/**
 * Medical Records which can be checked for Eligibility can implement this contract.
 *
 * Interface EligibilityCheckable
 */
interface EligibilityCheckable extends HasMedicalRecord
{
    /**
     * @return EligibilityJob
     */
    public function createAndProcessEligibilityJobFromMedicalRecord(): EligibilityJob;

    public function getEligibilityJob(): EligibilityJob;

    /**
     * This Model holds the data we need to make a request to an EHR API to get patient data.
     *
     * @return TargetPatient
     */
    public function getTargetPatient(): TargetPatient;
}
