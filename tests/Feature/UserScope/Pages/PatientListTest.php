<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Pages;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Testing\TestResponse;
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
    public function test_it_only_shows_same_practice_users()
    {
        $this->withPracticeScope()
            ->calling('GET', route('get.patientlist.index'), [
                'rows' => 'all',
            ])
            ->assertCallback(function (TestResponse $response, User $user) {
                $decoded = collect($response->decodeResponseJson());

                $this->assertTrue(collect($decoded->get('data'))->whereNotIn('program_id', array_merge($user->practices->pluck('id')->all(), [$user->program_id]))->isEmpty());
            });
    
        $this->withLocationScope()
            ->calling('GET', route('get.patientlist.index'), [
                'rows' => 'all',
            ])
            ->assert(
                new Practice('data', 'program_id'),
                new Location('data', 'location_id'),
            );
    }
}
