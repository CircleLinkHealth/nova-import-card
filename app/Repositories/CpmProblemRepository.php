<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use CircleLinkHealth\SharedModels\Entities\CpmProblem;

class CpmProblemRepository
{
    public function model()
    {
        return app(CpmProblem::class);
    }
}
