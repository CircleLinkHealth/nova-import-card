<?php

namespace Tests\Helpers;

use App\User;
use CircleLinkHealth\Customer\Entities\Role;

trait UserHelpers
{
    /**
     * Creates an admin user to be used with tests.
     *
     * @return User
     */
    private function createAdminUser()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $adminRole = Role::getIdsFromNames(['administrator']);
        $user->attachGlobalRole($adminRole);

        return $user;
    }
}
