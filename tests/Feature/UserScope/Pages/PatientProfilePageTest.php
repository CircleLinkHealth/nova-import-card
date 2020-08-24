<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Pages;

use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Testing\TestResponse;
use Tests\Feature\UserScope\TestCase as UserScopeTestCase;
use Tests\Feature\UserScope\Traits\AssertsLocationsAndPracticesWithCollection;

class PatientProfilePageTest extends UserScopeTestCase
{
    use AssertsLocationsAndPracticesWithCollection;

    public function test_patient_profile_page_dropdown_shows_providers_from_the_same_location_only()
    {
        $this->withLocationScope();

        $this->actor()->locations->each(function (Location $location) {
            $this->calling('GET', route('api.get.location.providers', [$location->practice_id, $location->id]))
                ->assertCallback(function (TestResponse $response, User $actor) {
                    User::ofType('provider')
                        ->whereIn('id', $this->extractResponseData($response)->pluck('id')->all())
                        ->with(['practices', 'locations'])
                        ->each(function ($provider) use ($actor) {
                            $this->assertPractices($actor, $provider->practices->pluck('id'));
                            $this->assertLocations($actor, $provider->locations->pluck('id'));
                        });
                });
        });
    }

    public function test_patient_profile_page_dropdown_shows_providers_from_the_same_practice_only()
    {
        $this->withPracticeScope();

        $this->actor()->locations->each(function (Location $location) {
            $this->calling('GET', route('api.get.location.providers', [$location->practice_id, $location->id]))
                ->assertCallback(function (TestResponse $response, User $actor) {
                    User::ofType('provider')
                        ->whereIn('id', $this->extractResponseData($response)->pluck('id')->all())
                        ->with(['practices'])
                        ->each(function ($provider) use ($actor) {
                            $this->assertPractices($actor, $provider->practices->pluck('id'));
                        });
                });
        });
    }
}
