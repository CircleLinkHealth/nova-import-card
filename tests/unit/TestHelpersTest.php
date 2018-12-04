<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\SetupTestCustomer;
use Tests\TestCase;

class TestHelpersTest extends TestCase
{
    use SetupTestCustomer,
        DatabaseTransactions;
    private $location;
    private $patient;

    private $practice;
    private $provider;
    private $total;

    public function setUp()
    {
        parent::setUp();

        $this->date = Carbon::today();

        //to test SetupTestCustomer Trait
        $this->practice = $this->createPractice();
        $this->location = $this->createLocation($this->practice);
        $this->provider = $this->createProvider($this->practice);
        $this->patient  = $this->createPatient($this->practice, $this->provider->id);

        $this->total = $this->createTestCustomerData();
    }

    public function test_setup_test_customer()
    {
        $this->assertInstanceOf('App\Practice', $this->practice);
        $this->assertInstanceOf('App\Location', $this->location);
        $this->assertInstanceOf('App\User', $this->patient);
        $this->assertInstanceOf('App\User', $this->provider);

        $this->assertInstanceOf('App\CarePlan', $this->patient->carePlan);

        $this->assertEquals($this->patient->program_id, $this->practice->id);
        $this->assertEquals($this->provider->program_id, $this->practice->id);

        //different data
        $this->assertInstanceOf('App\Location', $this->total['location']);
        $this->assertInstanceOf('App\User', $this->total['provider']);
        $this->assertInstanceOf('App\Practice', $this->total['practice']);
        foreach ($this->total['patients'] as $patient) {
            $this->assertInstanceOf('App\User', $patient);
            $this->assertEquals($patient->program_id, $this->total['practice']->id);
            $this->assertEquals($patient->billingProvider->first()->member_user_id, $this->total['provider']->id);
        }
    }
}
