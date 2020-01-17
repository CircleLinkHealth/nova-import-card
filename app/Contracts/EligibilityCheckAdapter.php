<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Adapters\EligibilityCheck;

use App\Contracts\HasMedicalRecord;
use CircleLinkHealth\Eligibility\Entities\EligibilityJob;

interface EligibilityCheckAdapter extends HasMedicalRecord
{
    /**
     * @throws \Exception
     */
    public function adaptToEligibilityJob(): EligibilityJob;
}
