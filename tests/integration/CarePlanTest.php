<?php

namespace Tests\integration;

use Tests\TestCase;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\UserHelpers;

class CarePlanTest extends TestCase
{
    use CarePlanHelpers,
        UserHelpers;

    protected $patients;

    protected $provider;

    public function setUp()
    {
        parent::setUp();

        $this->provider = $this->createUser();
        auth()->login($this->provider);
    }

    public function testCreatePatientWithSomeItemsChecked()
    {
        $this->patients[] = $patient = $this->createNewPatient();

        $this->fillCarePlan($patient, 2);
    }

    public function testCreatePatientWithAllItemsChecked()
    {
        $this->patients[] = $patient = $this->createNewPatient();

        $this->fillCarePlan($patient);
    }

    public function tearDown()
    {
        $this->report();

        parent::tearDown(); // TODO: Change the autogenerated stub
    }
}
