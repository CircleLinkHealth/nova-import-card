<?php

namespace Tests\unit;

use Tests\TestCase;
use App\CarePerson;
use App\User;
use Faker\Factory;
use Tests\Helpers\CarePlanHelpers;
use Tests\Helpers\UserHelpers;

class CareTeamReceivesAlertsTest extends TestCase
{
    use CarePlanHelpers,
        UserHelpers;
    /**
     * @var Faker\Factory $faker
     */
    protected $faker;

    /**
     * @var
     */
    protected $patient;

    /**
     * @var User $provider
     */
    protected $provider;

    public function test_it_returns_empty_collection_if_no_care_team()
    {
        $this->assertEmpty($this->patient->care_team_receives_alerts);
    }

    public function test_it_returns_only_one_user_if_duplicates_exist()
    {
        $i = 3;

        while ($i != 0) {
            $carePerson = CarePerson::create([
                'alert'          => true,
                'type'           => 'member',
                'user_id'        => $this->patient->id,
                'member_user_id' => $this->provider->id,
            ]);

            $i--;
        }

        $this->assertEquals(1, $this->patient->care_team_receives_alerts->count());
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
        $cp2 = $this->createUser(9);
        $carePerson = CarePerson::create([
            'alert'          => true,
            'type'           => 'member',
            'user_id'        => $this->patient->id,
            'member_user_id' => $cp2->id,
        ]);

        //add third care person
        $cp3 = $this->createUser(9);

        $cp2->forwardAlertsTo()->attach($cp3->id, [
            'name' => User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER,
        ]);

        $this->assertEquals(3, $this->patient->care_team_receives_alerts->count());
        $this->assertContains($cp3->id, $this->patient->care_team_receives_alerts->pluck('id'));
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
        $cp2 = $this->createUser(9);
        $carePerson = CarePerson::create([
            'alert'          => true,
            'type'           => 'member',
            'user_id'        => $this->patient->id,
            'member_user_id' => $cp2->id,
        ]);

        //add third care person
        $cp3 = $this->createUser(9);

        //set up forwarding
        $cp2->forwardAlertsTo()->attach($cp3->id, [
            'name' => User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER,
        ]);

        $this->assertEquals(2, $this->patient->care_team_receives_alerts->count());

        $ids = $this->patient->care_team_receives_alerts->pluck('id');

        $this->assertContains($this->provider->id, $ids);
        $this->assertContains($cp3->id, $ids);
        $this->assertNotContains($cp2->id, $ids);
    }

    public function test_it_returns_location_contacts_person_in_addition_to_bp()
    {
        $cp2 = $this->createUser(9);
        $carePerson = CarePerson::create([
            'alert'          => true,
            'type'           => 'member',
            'user_id'        => $this->patient->id,
            'member_user_id' => $cp2->id,
        ]);

        $this->patient->locations[0]->clinicalEmergencyContact()->attach($this->provider->id, [
            'name' => CarePerson::IN_ADDITION_TO_BILLING_PROVIDER,
        ]);

        $this->assertEquals(2, $this->patient->care_team_receives_alerts->count());
    }

    public function test_it_returns_only_location_contacts_person_instead_of_bp()
    {
        $cp2 = $this->createUser(9);
        $carePerson = CarePerson::create([
            'alert'          => true,
            'type'           => 'member',
            'user_id'        => $this->patient->id,
            'member_user_id' => $cp2->id,
        ]);

        $this->patient->locations[0]->clinicalEmergencyContact()->attach($this->provider->id, [
            'name' => CarePerson::INSTEAD_OF_BILLING_PROVIDER,
        ]);

        $this->assertEquals(1, $this->patient->care_team_receives_alerts->count());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->faker = Factory::create();
        $this->provider = $this->createUser(9);
        auth()->login($this->provider);
        $this->patient = $this->createUser(9, 'participant');

        foreach ($this->provider->locations as $location) {
            $location->clinicalEmergencyContact()->sync([]);
        }
    }
}
