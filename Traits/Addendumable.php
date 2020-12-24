<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Traits;

use CircleLinkHealth\SharedModels\Entities\Addendum;

trait Addendumable
{
    public function addendums()
    {
        return $this->morphMany(Addendum::class, 'addendumable');
    }
}
