<?php

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

    public function test_it_returns_members_if_they_exist()
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
        $carePerson = CarePerson::create([
            'alert'          => true,
            'type'           => 'member',
            'user_id'        => $this->patient->id,
            'member_user_id' => $cp3->id,
        ]);

        $this->assertEquals(3, $this->patient->care_team_receives_alerts->count());
    }

    protected function setUp()
    {
        parent::setUp();
        $this->faker = Factory::create();
        $this->provider = $this->createUser(9);
        auth()->login($this->provider);
        $this->patient = $this->createUser(9, 'participant');
    }

}
