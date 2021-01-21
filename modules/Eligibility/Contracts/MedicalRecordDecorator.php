<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Contracts;

use CircleLinkHealth\SharedModels\Entities\EligibilityJob;

interface MedicalRecordDecorator
{
    public function decorate(EligibilityJob $job): EligibilityJob;
}
