<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls;

use App\Algorithms\Calls\NurseFinder\NurseFinderContract;
use CircleLinkHealth\Customer\Entities\User;

class NurseFinderV2 implements NurseFinderContract
{
    public function find(int $patientUserId): ?User
    {
        // TODO: Implement find() method.
    }
}
