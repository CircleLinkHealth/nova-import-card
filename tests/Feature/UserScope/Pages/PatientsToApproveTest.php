<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Pages;

use Tests\Feature\UserScope\Assertions\Location;
use Tests\Feature\UserScope\Assertions\Practice;
use Tests\Feature\UserScope\TestCase as UserScopeTestCase;

class PatientsToApproveTest extends UserScopeTestCase
{
    public function test_user_scopes_on_patients_to_approve_list()
    {
        $searchTerm = 'Role:participant';
        
        $this->withPracticeScope()
            ->calling('GET', $route = route('get.patientlist.index', [$searchTerm]), $params = [
                'rows' => 'all',
                'patientsPendingAuthUserApproval',
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
