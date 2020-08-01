<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use App\Traits\SetupTestCustomerTrait;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\User;
use Faker\Factory;
use Tests\CustomerTestCase;
use Tests\Helpers\CarePlanHelpers;

class CareTeamReceivesAlertsTest extends CustomerTestCase
{
    use CarePlanHelpers;
    use SetupTestCustomerTrait;
    /**
     * @var Faker\Factory
     */
    protected $faker;

    /**
     * @var
     */
    protected $patient;

    protected $practice;

    /**
     * @var \CircleLinkHealth\Customer\Entities\User
     */
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $data           = $this->createTestCustomerData(1);
        $this->practice = $data['practice'];

        $this->faker = Factory::create();

        $this->provider = $this->provider();

        auth()->login($this->provider);
        $this->patient = $this->patient();

        foreach ($this->provider->locations as $location) {
            $location->clinicalEmergencyContact()->sync([]);
        }
    }

    public function test_it_does_not_return_instead_of_provider()
    {
        //add first care person
        $carePerson = CarePerson::create([
            'alert'          => true,
            'type'           => 'member',
            'user_id'        => $this->patient->id,
            'member_user_id' => $this->provider->id,
        ]);

        //add second care person
        $cp2        = $this->createUser($this->practice->id);
        $carePerson = CarePerson::create([
            'alert'          => true,
            'type'           => 'member',
            'user_id'        => $this->patient->id,
            'member_user_id' => $cp2->id,
        ]);

        //add third care person
        $cp3 = $this->createUser($this->practice->id);

        //set up forwarding
        $cp2->forwardAlertsTo()->attach($cp3->id, [
            'name' => User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER,
        ]);

        $this->assertEquals(2, $this->patient->getCareTeamReceivesAlerts()->count());

        $ids = $this->patient->getCareTeamReceivesAlerts()->pluck('id');

        $this->assertContains($this->provider->id, $ids);
        $this->assertContains($cp3->id, $ids);
        $this->assertNotContains($cp2->id, $ids);
    }

    public function test_it_returns_empty_collection_if_no_care_team()
    {
        $this->assertEmpty($this->patient->getCareTeamReceivesAlerts());
    }

    public function test_it_returns_in_addition_to_provider()
    {
        //add first care person
        $carePerson = CarePerson::create([
            'alert'          => true,
            'type'           => 'member',
            'user_id'        => $this->patient->id,
            'member_user_id' => $this->provider->id,
        ]);

        //add second care person
        $cp2        = $this->createUser($this->practice->id);
        $carePerson = CarePerson::create([
            'alert'          => true,
            'type'           => 'member',
            'user_id'        => $this->patient->id,
            'member_user_id' => $cp2->id,
        ]);

        //add third care person
        $cp3 = $this->createUser($this->practice->id);

        $cp2->forwardAlertsTo()->attach($cp3->id, [
            'name' => User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER,
        ]);

        $this->assertEquals(3, $this->patient->getCareTeamReceivesAlerts()->count());
        $this->assertContains($cp3->id, $this->patient->getCareTeamReceivesAlerts()->pluck('id'));
    }

    public function test_it_returns_location_contacts_person_in_addition_to_bp()
    {
        $cp2        = $this->createUser($this->practice->id);
        $carePerson = CarePerson::create([
            'alert'          => true,
            'type'           => 'member',
            'user_id'        => $this->patient->id,
            'member_user_id' => $cp2->id,
        ]);

        $this->patient->locations[0]->clinicalEmergencyContact()->attach($this->provider->id, [
            'name' => CarePerson::IN_ADDITION_TO_BILLING_PROVIDER,
        ]);

        $this->assertEquals(2, $this->patient->getCareTeamReceivesAlerts()->count());
    }

    public function test_it_returns_only_location_contacts_person_instead_of_bp()
    {
        $cp2        = $this->createUser($this->practice->id);
        $carePerson = CarePerson::create([
            'alert'          => true,
            'type'           => 'member',
            'user_id'        => $this->patient->id,
            'member_user_id' => $cp2->id,
        ]);

        $this->patient->locations[0]->clinicalEmergencyContact()->attach($this->provider->id, [
            'name' => CarePerson::INSTEAD_OF_BILLING_PROVIDER,
        ]);

        $this->assertEquals(1, $this->patient->getCareTeamReceivesAlerts()->count());
    }

    public function test_it_returns_only_one_user_if_duplicates_exist()
    {
        $i = 3;

        while (0 != $i) {
            $carePerson = CarePerson::create([
                'alert'          => true,
                'type'           => 'member',
                'user_id'        => $this->patient->id,
                'member_user_id' => $this->provider->id,
            ]);

            --$i;
        }

        $this->assertEquals(1, $this->patient->getCareTeamReceivesAlerts()->count());
    }
}
