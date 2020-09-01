<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use CircleLinkHealth\CcmBilling\Entities\LocationProblemService;

interface LocationProblemServiceRepository
{
    public function store(int $locationId, int $cpmProblemId, int $chargeableServiceId): LocationProblemService;
}
