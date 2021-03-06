<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Contracts;

use CircleLinkHealth\Eligibility\MedicalRecordImporter\HasMedicalRecord;
use CircleLinkHealth\SharedModels\Entities\EligibilityJob;

interface EligibilityCheckAdapter extends HasMedicalRecord
{
    /**
     * @throws \Exception
     */
    public function adaptToEligibilityJob(): EligibilityJob;
}
