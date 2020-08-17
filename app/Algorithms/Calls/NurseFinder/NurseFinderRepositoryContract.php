<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls\NurseFinder;

use CircleLinkHealth\Customer\Entities\User;

interface NurseFinderRepositoryContract
{
    public function find(int $patientUserId): ?User;
}
