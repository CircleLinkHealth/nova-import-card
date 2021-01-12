<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Contracts;

use CircleLinkHealth\Customer\Entities\User;

interface NurseFinderRepositoryContract
{
    public function assign(int $patientUserId, int $nurseUserId): bool;

    public function find(int $patientUserId): ?User;
}
