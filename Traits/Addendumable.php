<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use App\Models\Addendum;

trait Addendumable
{
    public function addendums()
    {
        return $this->morphMany(Addendum::class, 'addendumable');
    }
}
