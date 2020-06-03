<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Chargeable extends MorphPivot
{
    protected $table = 'chargeables';
}
