<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Traits\SetupTestCustomerTrait;
use Carbon\Carbon;
use Tests\TestCase;

class TestHelpersTest extends TestCase
{
    use SetupTestCustomerTrait;
    private $location;
    private $patient;

    private $practice;
    private $provider;
    private $total;

    protected function setUp(): void
    {
        parent::setUp();

        $this->date = Carbon::today();

        //to test SetupTestCustomerTrait Trait
        $this->practice = $this->createPractice();
        $this->location = $this->createLocation($this->practice);
        $this->provider = $this->createProvider($this->practice);
        $this->patient  = $this->createPatient($this->practice, $this->provider->id);

        $this->total = $this->createTestCustomerData();
    }

    public function test_setup_test_customer()
    {
        $this->assertInstanceOf('CircleLinkHealth\Customer\Entities\Practice', $this->practice);
        $this->assertInstanceOf('CircleLinkHealth\Customer\Entities\Location', $this->location);
        $this->assertInstanceOf('CircleLinkHealth\Customer\Entities\User', $this->patient);
        $this->assertInstanceOf('CircleLinkHealth\Customer\Entities\User', $this->provider);

        $this->assertInstanceOf('CircleLinkHealth\SharedModels\Entities\CarePlan', $this->patient->carePlan);

        $this->assertEquals($this->patient->program_id, $this->practice->id);
        $this->assertEquals($this->provider->program_id, $this->practice->id);

        //different data
        $this->assertInstanceOf('CircleLinkHealth\Customer\Entities\Location', $this->total['location']);
        $this->assertInstanceOf('CircleLinkHealth\Customer\Entities\User', $this->total['provider']);
        $this->assertInstanceOf('CircleLinkHealth\Customer\Entities\Practice', $this->total['practice']);
        foreach ($this->total['patients'] as $patient) {
            $this->assertInstanceOf('CircleLinkHealth\Customer\Entities\User', $patient);
            $this->assertEquals($patient->program_id, $this->total['practice']->id);
            $this->assertEquals($patient->billingProvider->first()->member_user_id, $this->total['provider']->id);
        }
    }
}
