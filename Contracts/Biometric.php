<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Contracts;

use CircleLinkHealth\Customer\Entities\User;

interface Biometric
{
    public function getUserValues(User $user);
}
