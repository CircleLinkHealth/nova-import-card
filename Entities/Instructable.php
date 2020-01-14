<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use CircleLinkHealth\SharedModels\Entities\CpmInstruction;

trait Instructable
{
    /**
     * Use this relationships on Models that need to have Instructions.
     *
     * @return mixed
     */
    public function cpmInstructions()
    {
        return $this->morphToMany(CpmInstruction::class, 'instructable');
    }
}
