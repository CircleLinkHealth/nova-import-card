<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Contracts;

use App\Contracts\HasMedicalRecord;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use App\TargetPatient;

/**
 * A "Checkable" is an object encapsulating behavior and data necessary to determine whether the patient whose materials are encapsulated by the "Checkable" is eligible to receive service from CLH.
 *
 * Interface Checkable
 */
interface Checkable extends HasMedicalRecord
{
    /**
     * Creates an EligibilityJob using the MedicalRecord provided to the Checkable, and stores it on the instance.
     *
     * @return EligibilityJob
     */
    public function createAndProcessEligibilityJobFromMedicalRecord(): EligibilityJob;

    /**
     * Get the Eligibility Job stored on the instance.
     *
     * @return EligibilityJob
     */
    public function getEligibilityJob(): EligibilityJob;

    /**
     * This Model holds the data we need to make a request to an EHR API to get patient data. This includes data such as the the patient/practice/department ID in the EHR,.
     *
     * @return TargetPatient
     */
    public function getTargetPatient(): TargetPatient;
}
