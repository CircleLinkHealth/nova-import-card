<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Contracts;

use CircleLinkHealth\SharedModels\Entities\EligibilityJob;
use CircleLinkHealth\Eligibility\MedicalRecordImporter\HasMedicalRecord;

interface EligibilityCheckAdapter extends HasMedicalRecord
{
    /**
     * @throws \Exception
     */
    public function adaptToEligibilityJob(): EligibilityJob;
}
