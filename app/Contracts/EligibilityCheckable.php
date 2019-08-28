<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use App\Contracts\Importer\MedicalRecord\MedicalRecord;
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
     * @param MedicalRecord $medicalRecord
     *
     * @return EligibilityJob
     */
    public function createEligibilityJobFromMedicalRecord(): EligibilityJob;

    /**
     * This Model holds the data we need to make a request to an EHR API to get patient data.
     *
     * @return TargetPatient
     */
    public function targetPatient(): TargetPatient;
}
