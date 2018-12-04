<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts\Models\CPM;

use App\User;

interface Biometric
{
    public function getUserValues(User $user);
}
