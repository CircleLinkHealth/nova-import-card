<?php

namespace Tests\Unit;

use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\SetupTestCustomer;
use Tests\TestCase;
use Faker\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationsTest extends TestCase
{
    use CarePlanHelpers,
        SetupTestCustomer;
    /**
     * @var Faker\Factory $faker
     */
    protected $faker;

    /**
     * @var
     */
    protected $patient;

    protected $practice;

    /**
     * @var User $provider
     */
    protected $provider;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    protected function setUp()
    {
        parent::setUp();

        $data = $this->createTestCustomerData(1);
        $this->practice = $data['practice'];


        $this->faker = Factory::create();

        $this->provider = $this->createUser($this->practice->id, 'provider');

        auth()->login($this->provider);
        $this->patient = $this->createUser($this->practice->id, 'participant');

        foreach ($this->provider->locations as $location) {
            $location->clinicalEmergencyContact()->sync([]);
        }
    }
}
