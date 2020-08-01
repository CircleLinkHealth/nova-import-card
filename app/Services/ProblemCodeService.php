<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\SharedModels\Entities\ProblemCodeSystem;

class ProblemCodeService
{
    public function system($id)
    {
        return ProblemCodeSystem::findOrFail($id);
    }

    public function systems()
    {
        return ProblemCodeSystem::get();
    }
}
