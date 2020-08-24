<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature\UserScope\Pages;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Testing\TestResponse;
use Tests\Feature\UserScope\TestCase as UserScopeTestCase;
use Tests\Feature\UserScope\Traits\ExtractsDataFromWebixResponse;

class Under20MinutesReportTest extends UserScopeTestCase
{
    use ExtractsDataFromWebixResponse;

    public function test_under_20_minutes_report_page_shows_patients_from_the_same_location_only()
    {
        $this->withLocationScope()
            ->calling('GET', route('patient.reports.u20'))
            ->assertCallback(function (TestResponse $response, User $actor) {
                $responseData = $this->extractWebixResponseData($response, 'activity_json');

                $this->assertPractice($actor, $responseData, 'practice_id');
                $this->assertLocation($actor, $responseData, 'location_id');
            });
    }

    public function test_under_20_minutes_report_page_shows_patients_from_the_same_practice_only()
    {
        $this->withPracticeScope()
            ->calling('GET', route('patient.reports.u20'))
            ->assertCallback(function (TestResponse $response, User $actor) {
                $responseData = $this->extractWebixResponseData($response, 'activity_json');

                $this->assertPractice($actor, $responseData, 'practice_id');
            });
    }
}
