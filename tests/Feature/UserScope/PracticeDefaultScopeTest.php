<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope;

use CircleLinkHealth\Customer\Entities\User;
use Tests\CustomerTestCase;

class PracticeDefaultScopeTest extends CustomerTestCase
{
    public function test_it_adds_default_scope_to_practice_staff_roles()
    {
        self::assertNotEquals(User::SCOPE_LOCATION, $this->provider()->scope);

        $this->assertDatabaseMissing('practices', [
            'default_user_scope' => User::SCOPE_LOCATION,
            'id'                 => $this->practice()->id,
        ]);

        $updated = $this->practice()->update([
            'default_user_scope' => User::SCOPE_LOCATION,
        ]);

        $this->assertDatabaseHas('practices', [
            'default_user_scope' => User::SCOPE_LOCATION,
            'id'                 => $this->practice()->id,
        ]);

        $this->resetProvider();
        self::assertEquals(User::SCOPE_LOCATION, $this->provider()->scope);
    }
}
