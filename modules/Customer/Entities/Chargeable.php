<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

/**
 * CircleLinkHealth\Customer\Entities\Chargeable.
 *
 * @property int                             $chargeable_service_id
 * @property int                             $chargeable_id
 * @property string                          $chargeable_type
 * @property float|null                      $amount
 * @property int                             $is_fulfilled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Chargeable newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Chargeable newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Chargeable query()
 * @mixin \Eloquent
 */
class Chargeable extends MorphPivot
{
    protected $table = 'chargeables';
}
