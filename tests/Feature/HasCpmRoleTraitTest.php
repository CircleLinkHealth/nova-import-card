<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use CircleLinkHealth\Customer\Entities\Role;
use Tests\CustomerTestCase;

class HasCpmRoleTraitTest extends CustomerTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_cache_resets_after_role_change()
    {
        $patient = $this->patient();
        $this->assertTrue($patient->hasRole('participant'));
        $this->assertTrue($patient->isParticipant());

        $roleIds = Role::getIdsFromNames(['provider']);
        $this->assertNotEmpty($roleIds);

        $patient->roles()->sync($roleIds);
        $this->assertFalse($patient->hasRole('participant'));
        $this->assertFalse($patient->isParticipant());
        $this->assertTrue($patient->hasRole('provider'));
        $this->assertTrue($patient->isProvider());
    }
}
