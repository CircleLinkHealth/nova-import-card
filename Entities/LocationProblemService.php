<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\SharedModels\Entities\CpmProblem;
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
 * @property ChargeableService      $chargeableService
 * @property CpmProblem             $cpmProblem
 * @property LocationProblemService $location
 */
class LocationProblemService extends Pivot
{
    protected $fillable = [
        'cpm_problem_id',
        'location_id',
        'chargeable_service_id',
    ];

    protected $table = 'location_problem_services';

    public function chargeableService()
    {
        return $this->belongsTo(ChargeableService::class, 'chargeable_service_id');
    }

    public function cpmProblem()
    {
        return $this->belongsTo(CpmProblem::class, 'cpm_problem_id');
    }

    public function location()
    {
        return $this->belongsTo(LocationProblemService::class, 'location_id');
    }
}
