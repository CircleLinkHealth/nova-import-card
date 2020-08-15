<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls\NurseFinder;

use CircleLinkHealth\Customer\Entities\User;

interface NurseFinderContract
{
    public function find(int $patientUserId): ?User;
}
