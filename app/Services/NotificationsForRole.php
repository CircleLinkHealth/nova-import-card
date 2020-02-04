<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use CircleLinkHealth\Customer\Entities\Role;

class NotificationsForRole
{
    /**
     * @var Role
     */
    private $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function subscriptionsForMail()
    {
        return \Config::get('notifications-per-role')[$this->role->name]['email'];
    }
}
