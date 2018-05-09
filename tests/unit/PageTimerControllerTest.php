<?php

namespace Tests\Unit;

use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class PageTimerControllerTest extends TestCase
{
    use DatabaseTransactions,
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
                    'start_time' => Carbon::now()->toDateTimeString(),
                    'duration'   => 10,
                    'url'    => '',
                    'url_short'   => '',
                    'name'   => 'Test activity',
                    'title'      => 'some.route',
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

    protected function setUp()
    {
        parent::setUp();
        $this->provider = factory(User::class)->create();
        $this->patient  = factory(User::class)->create();

        //add provider role
        $role = Role::where('name', 'provider')->first();
        $this->provider->roles()->attach($role->id);
    }
}
