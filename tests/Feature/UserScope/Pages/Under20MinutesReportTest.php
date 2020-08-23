<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Pages;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Testing\TestResponse;
use Tests\Feature\UserScope\TestCase as UserScopeTestCase;

class Under20MinutesReportTest extends UserScopeTestCase
{
    public function test_user_scopes_on_patients_to_approve_list()
    {
        $this->withPracticeScope()
            ->calling('GET', $route = route('patient.reports.u20'))
            ->assertCallback(function (TestResponse $response, User $actor) {
                $responseData = $this->extract($response);

                $this->assertPractice($actor, $responseData, 'practice_id');
            });

        $this->withLocationScope()
            ->calling('GET', $route)
            ->assertCallback(function (TestResponse $response, User $actor) {
                $responseData = $this->extract($response);

                $this->assertPractice($actor, $responseData, 'practice_id');
                $this->assertLocation($actor, $responseData, 'location_id');
            });
    }

    private function extract(TestResponse $response)
    {
        return collect(json_decode(ltrim($this->extractResponseData($response)->get('activity_json'), 'data:'), true));
    }
}
