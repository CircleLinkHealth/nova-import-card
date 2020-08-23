<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Pages;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Testing\TestResponse;
use Tests\Feature\UserScope\TestCase as UserScopeTestCase;

class NotesReportTest extends UserScopeTestCase
{
    public function test_user_scopes_on_patients_to_approve_list()
    {
        $this->withPracticeScope()
            ->calling('GET', $route = route('patient.note.listing'))
            ->assertCallback(function (TestResponse $response, User $actor) {
                $responseData = $this->extractResponseData($response);

                $responseData->get('providers')->each(function ($name, $id) use ($actor) {
                    $this->assertPractice($actor, $this->getPractices($id));
                });
            });

        $this->withLocationScope()
            ->calling('GET', $route)
            ->assertCallback(function (TestResponse $response, User $actor) {
                $responseData = $this->extractResponseData($response);

                $responseData->get('providers')->each(function ($name, $id) use ($actor) {
                    $this->assertPractice($actor, $this->getPractices($id));
                    $this->assertLocation($actor, $this->getLocations($id));
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
