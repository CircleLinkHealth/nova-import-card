<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use CircleLinkHealth\CcmBilling\Contracts\LocationProblemServiceRepository as Repository;
use CircleLinkHealth\CcmBilling\Entities\LocationProblemService;

class LocationProblemServiceRepository implements Repository
{
    public function store(int $locationId, int $cpmProblemId, int $chargeableServiceId): LocationProblemService
    {
        return LocationProblemService::updateOrCreate([
            'location_id'           => $locationId,
            'cpm_problem_id'        => $cpmProblemId,
            'chargeable_service_id' => $chargeableServiceId,
        ]);
    }
}
