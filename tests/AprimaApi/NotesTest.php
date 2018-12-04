<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\AprimaApi;

use App\PatientReports;
use Tests\Helpers\AuthenticatesApiUsers;
use Tests\TestCase;

class NotesTest extends TestCase
{
    use AuthenticatesApiUsers;

    public function setUp()
    {
        parent::setUp();

        $this->authenticate();
    }

    public function test_returns_notes()
    {
        $allNotes = PatientReports::whereFileType('note')
            ->where('location_id', 40)
            ->count();

        $response = $this->call('GET', "/api/v1.0/notes?access_token={$this->getAccessToken()}", [
            'send_all' => true,
        ]);

        $this->assertResponseOk();

        $countReturned = count(json_decode($response->getContent(), true));

        $this->assertEquals($allNotes, $countReturned);
    }
}
