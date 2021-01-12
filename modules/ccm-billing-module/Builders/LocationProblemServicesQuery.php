<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use CircleLinkHealth\SharedModels\Entities\CpmProblem;
use Illuminate\Database\Eloquent\Builder;

trait LocationProblemServicesQuery
{
    public function cpmProblemsForLocation(int $locationId): Builder
    {
        return CpmProblem::ofLocation($locationId);
    }
}
