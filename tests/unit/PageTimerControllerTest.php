<?php

namespace Tests\Unit;

use App\Practice;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\Helpers\UserHelpers;
use Tests\TestCase;

class PageTimerControllerTest extends TestCase
{
    use DatabaseTransactions,
        UserHelpers,
        WithoutMiddleware;

    private $patient;
    private $provider;

    public function test_ccm_time_is_stored()
    {
        $response = $this->json('POST', route('api.pagetracking'), [
            'patientId'  => $this->patient->id,
            'providerId' => $this->provider->id,
            'programId'  => '',
            'ipAddr'     => '',
            'submitUrl'  => 'url',
            'activities' => [
                [
                    'start_time'    => Carbon::now()->toDateTimeString(),
                    'duration'      => 10,
                    'url'           => '',
                    'url_short'     => '',
                    'name'          => 'Test activity',
                    'title'         => 'some.route',
                    'is_behavioral' => false,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('lv_activities', [
            'patient_id'  => $this->patient->id,
            'provider_id' => $this->provider->id,
            'duration'    => 10,
            'type'        => 'Test activity',
        ]);
    }

    public function test_bhi_time_is_stored()
    {
        $response = $this->json('POST', route('api.pagetracking'), [
            'patientId'  => $this->patient->id,
            'providerId' => $this->provider->id,
            'programId'  => '',
            'ipAddr'     => '',
            'submitUrl'  => 'url',
            'activities' => [
                [
                    'start_time'    => Carbon::now()->toDateTimeString(),
                    'duration'      => 10,
                    'url'           => '',
                    'url_short'     => '',
                    'name'          => 'Test activity',
                    'title'         => 'some.route',
                    'is_behavioral' => true,

                ],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('lv_activities', [
            'patient_id'    => $this->patient->id,
            'provider_id'   => $this->provider->id,
            'duration'      => 10,
            'type'          => 'Test activity',
            'is_behavioral' => true,
        ]);
    }

    public function test_nurse_rates_for_bhi_over_20_and_ccm_over_20()
    {
        $response = $this->json('POST', route('api.pagetracking'), [
            'patientId'  => $this->patient->id,
            'providerId' => $this->provider->id,
            'programId'  => '',
            'ipAddr'     => '',
            'submitUrl'  => 'url',
            'activities' => [
                [
                    'start_time'    => Carbon::now()->toDateTimeString(),
                    'duration'      => 18,
                    'url'           => '',
                    'url_short'     => '',
                    'name'          => 'Test bhi activity 1',
                    'title'         => 'some.route',
                    'is_behavioral' => true,
                ],
                [
                    'start_time'    => Carbon::now()->toDateTimeString(),
                    'duration'      => 8,
                    'url'           => '',
                    'url_short'     => '',
                    'name'          => 'Test bhi activity 2',
                    'title'         => 'some.route',
                    'is_behavioral' => true,
                ],
                [
                    'start_time'    => Carbon::now()->toDateTimeString(),
                    'duration'      => 3,
                    'url'           => '',
                    'url_short'     => '',
                    'name'          => 'Test ccm activity 1',
                    'title'         => 'some.route',
                    'is_behavioral' => true,
                ],
                [
                    'start_time'    => Carbon::now()->toDateTimeString(),
                    'duration'      => 22,
                    'url'           => '',
                    'url_short'     => '',
                    'name'          => 'Test ccm activity 2',
                    'title'         => 'some.route',
                    'is_behavioral' => false,
                ],
            ],
        ]);

        $response->assertStatus(201);


    }

    protected function setUp()
    {
        parent::setUp();
        $this->practice = factory(Practice::class)->create();
        $this->provider = $this->createUser($this->practice->id, 'care-center');
        $this->patient  = factory(User::class)->create();

        //add provider role
        $role = Role::where('name', 'provider')->first();
        $this->provider->roles()->attach($role->id);
    }
}
