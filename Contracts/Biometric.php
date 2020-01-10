<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CarePlanModels\Contracts;

use CircleLinkHealth\Customer\Entities\User;

interface Biometric
{
    public function getUserValues(User $user);
}
