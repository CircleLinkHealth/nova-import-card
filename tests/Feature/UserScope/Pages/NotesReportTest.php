<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Pages;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Tests\Feature\UserScope\TestCase as UserScopeTestCase;

class NotesReportTest extends UserScopeTestCase
{
    public function assertLocations(User $actor, Collection $locationIds)
    {
        return $locationIds->intersect($actor->locations->pluck('id'))->count() > 1;
    }

    public function assertPractices(User $actor, Collection $practiceIds)
    {
        return $practiceIds->intersect($actor->practices->pluck('id'))->count() > 1;
    
    }

    public function test_notes_report_page_shows_patients_from_the_same_location_only()
    {
        $this->withLocationScope()
            ->calling('GET', route('patient.note.listing'))
            ->assertCallback(function (TestResponse $response, User $actor) {
                $responseData = $this->extractResponseData($response);

                $responseData->get('providers')->each(function ($name, $id) use ($actor) {
                    $this->assertPractices($actor, $this->getPractices($id));
                    $this->assertLocations($actor, $this->getLocations($id));
                });
            });
    }

    public function test_notes_report_page_shows_patients_from_the_same_practice_only()
    {
        $this->withPracticeScope()
            ->calling('GET', route('patient.note.listing'))
            ->assertCallback(function (TestResponse $response, User $actor) {
                $responseData = $this->extractResponseData($response);

                $responseData->get('providers')->each(function ($name, $id) use ($actor) {
                    $this->assertPractices($actor, $this->getPractices($id));
                });
            });
    }

    private function getLocations($id)
    {
        return User::without(['perms', 'roles'])->with('locations')->findOrFail($id)->locations->pluck('id');
    }

    private function getPractices($id)
    {
        return User::without(['perms', 'roles'])->with('practices')->findOrFail($id)->practices->pluck('id');
    }
}
