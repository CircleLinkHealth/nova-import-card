<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Models\Addendum;

trait IsAddendumable
{
    public function addendums()
    {
        return $this->morphMany(Addendum::class, 'addendumable');
    }
}
