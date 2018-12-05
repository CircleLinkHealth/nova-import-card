<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits\Relationships;

use App\Models\CCD\CcdVendor;

trait BelongsToVendor
{
    public function vendor()
    {
        return $this->belongsTo(CcdVendor::class);
    }
}
