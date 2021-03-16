<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function create()
    {
        return true;
    }

    public function delete()
    {
        return true;
    }

    public function update()
    {
        return true;
    }

    public function view()
    {
        return true;
    }
}
