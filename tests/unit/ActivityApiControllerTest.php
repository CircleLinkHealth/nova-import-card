<?php

namespace Tests\Unit;

use App\Activity;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class ActivityApiControllerTest extends TestCase
{
    use WithoutMiddleware,
        UserHelpers;

    private $provider;
    private $patient;
    private $activities;

    public function test_ccm_time_from_provider_to_patient()
    {
        $sum = $this->activities->sum('duration');


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

    public function test_total_ccm_time_for_patient_for_current_month()
    {
        $sum = $this->activities->sum('duration');


        $response = $this->json(
            'GET',
            route('get.total.ccm.time', [
                'patientId'  => $this->patient->id,
            ])
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                $this->patient->id => $sum
            ]);
    }

    public function test_total_ccm_time_returns_404_if_no_patient_id_is_given()
    {
        $response = $this->json(
            'GET',
            route('get.total.ccm.time', [
                'patientId'  => null,
            ])
        );

        $response
            ->assertStatus(404);

        $response = $this->json(
            'GET',
            route('get.total.ccm.time', [
                'patientId'  => '',
            ])
        );

        $response
            ->assertStatus(404);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->provider = factory(User::class)->create();
        $this->patient  = factory(User::class)->create();
        $this->activities = factory(Activity::class, 10)->create([
            'patient_id' => $this->patient->id,
            'provider_id' => $this->provider->id,
            'logger_id' => $this->provider->id,
        ]);
    }
}
