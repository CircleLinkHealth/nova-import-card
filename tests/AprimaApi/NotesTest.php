<?php

use App\PatientReports;
use Tests\Helpers\AuthenticatesApiUsers;

class NotesTest extends TestCase
{
    use AuthenticatesApiUsers;

    public function setUp()
    {
        parent::setUp();

        $this->authenticate();
    }

    public function testReturnsNotes()
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
