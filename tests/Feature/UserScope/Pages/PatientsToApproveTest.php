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
    public function test_patients_to_approve_list_page_shows_patients_from_the_same_location_only()
    {
        $this->withLocationScope()
            ->calling('GET', $this->route(), $this->params())
            ->assert(
                new Practice('data', 'program_id'),
                new Location('data', 'location_id'),
            );
    }

    public function test_patients_to_approve_list_page_shows_patients_from_the_same_practice_only()
    {
        $this->withPracticeScope()
            ->calling('GET', $this->route(), $this->params())
            ->assert(new Practice('data', 'program_id'));
    }

    private function params()
    {
        return [
            'rows' => 'all',
            'patientsPendingAuthUserApproval',
        ];
    }

    private function route()
    {
        $searchTerm = 'Role:participant';

        return route('get.patientlist.index', [$searchTerm]);
    }
}
