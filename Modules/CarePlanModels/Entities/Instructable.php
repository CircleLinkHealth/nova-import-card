<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models\CPM;

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
