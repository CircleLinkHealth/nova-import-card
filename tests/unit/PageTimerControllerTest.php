<?php

namespace Tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class PageTimerControllerTest extends TestCase
{
    use DatabaseTransactions,
        WithoutMiddleware;

    private $patient;
    private $provider;

    protected function setUp()
    {
        parent::setUp();
        $this->provider = factory(User::class)->create();
        $this->patient = factory(User::class)->create();
    }

    public function test_ccm_time_is_stored()
    {
        $response = $this->json('POST', route('api.pagetracking'), [
            'patientId' => $this->patient->id,
            'providerId' => $this->provider->id,
            'totalTime' => 0,
            'programId' => '',
            'urlFull' => '',
            'urlShort' => '',
            'ipAddr' => '',
            'activity' => 'Test activity',
            'title',
            'submitUrl',
            'startTime',
        ]);
    }
}
