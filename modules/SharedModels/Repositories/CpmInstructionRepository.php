<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Repositories;

use CircleLinkHealth\SharedModels\Entities\CpmInstruction;

class CpmInstructionRepository
{
    public function model()
    {
        return app(CpmInstruction::class);
    }
}
