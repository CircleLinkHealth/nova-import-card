<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * CircleLinkHealth\CcmBilling\Entities\LocationProblemService.
 *
 * @property int                             $id
 * @property int                             $location_id
 * @property int                             $cpm_problem_id
 * @property int                             $chargeable_service_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method   static                          \Illuminate\Database\Eloquent\Builder|LocationProblemService newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|LocationProblemService newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|LocationProblemService query()
 * @mixin \Eloquent
 */
class LocationProblemService extends Pivot
{
    protected $fillable = [
        'cpm_problem_id',
        'location_id',
        'chargeable_service_id',
    ];

    protected $table = 'location_problem_services';
}
