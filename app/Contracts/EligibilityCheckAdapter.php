<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Adapters\EligibilityCheck;

use App\Contracts\HasMedicalRecord;
use App\EligibilityJob;

interface EligibilityCheckAdapter extends HasMedicalRecord
{
    /**
     * @throws \Exception
     *
     * @return EligibilityJob
     */
    public function adaptToEligibilityJob(): ?EligibilityJob;
}
