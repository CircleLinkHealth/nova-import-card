<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Pages;

use Tests\Feature\UserScope\Assertions\Location;
use Tests\Feature\UserScope\Assertions\Practice;
use Tests\Feature\UserScope\TestCase as UserScopeTestCase;

class PatientListTest extends UserScopeTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_scopes_on_patient_list_page()
    {
        $this->withPracticeScope()
            ->calling('GET', $route = route('get.patientlist.index'), $params = [
                'rows' => 'all',
            ])
            ->assert(new Practice('data', 'program_id'));

        $this->withLocationScope()
            ->calling('GET', $route, $params)
            ->assert(
                new Practice('data', 'program_id'),
                new Location('data', 'location_id'),
            );
    }
}
