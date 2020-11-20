<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use CircleLinkHealth\CcmBilling\Entities\LocationProblemService;
use Illuminate\Database\Eloquent\Collection;

interface LocationProblemServiceRepository
{
    public function problemsForLocation(int $locationId): Collection;

    public function store(int $locationId, int $cpmProblemId, int $chargeableServiceId): LocationProblemService;
}
