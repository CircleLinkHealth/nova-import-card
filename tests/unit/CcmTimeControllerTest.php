<?php

namespace Tests\Unit;

use App\Activity;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class CcmTimeControllerTest extends TestCase
{
    use DatabaseTransactions,
        WithoutMiddleware,
        UserHelpers;

    private $provider;
    private $patient;

    public function test_ccm_time_from_provider_to_patient()
    {
        $activities = factory(Activity::class, 10)->create([
            'patient_id' => $this->patient->id,
            'provider_id' => $this->provider->id,
            'logger_id' => $this->provider->id,
        ]);

        $sum = $activities->sum('duration');


        $response = $this->json(
            'GET',
            route('get.ccm.time.from.to', [
                'providerId' => $this->provider->id,
                'patientId'  => $this->patient->id,
            ])
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                $this->patient->id => $sum
            ]);

    }

    protected function setUp()
    {
        parent::setUp();

        $this->provider = factory(User::class)->create();
        $this->patient  = factory(User::class)->create();
    }
}
