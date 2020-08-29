<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LocationProblemService extends Pivot
{
    protected $fillable = [
        'cpm_problem_id',
        'location_id',
        'chargeable_service_id',
    ];

    protected $table = 'location_problem_services';
}
