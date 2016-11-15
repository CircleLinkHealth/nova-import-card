<?php

use App\User;
use Faker\Factory;

class OnboardingTest extends TestCase
{
    protected $faker;
    protected $numberOfLocations;

    /**
     * @var User $provider
     */
    protected $provider;

    /**
     * Check that the form to create a User is there.
     */
    public function test_it_shows_create_lead_user_form()
    {
        $this->visit(route('get.onboarding.create.program.lead.user'))
            ->see('firstName')
            ->see('lastName')
            ->see('email')
            ->see('password');
    }

    public function test_it_stores_a_practice()
    {
        $name = $this->faker->company;

        $this->actingAs($this->provider)
            ->visit(route('get.onboarding.create.practice'))
            ->type($name, 'name')
            ->type($this->numberOfLocations, 'numberOfLocations')
            ->press('create-practice');

        $this->seeInDatabase('practices', [
            'name'         => str_slug($name),
            'display_name' => $name,
            'user_id'      => auth()->user()->id,
        ]);
    }

    public function test_it_stores_locations()
    {
        $this->actingAs($this->provider)
            ->visit(route('get.onboarding.create.locations', [
                'numberOfLocations' => $this->numberOfLocations,
            ]));


        for ($i = 1; $i <= $this->numberOfLocations; $i++) {
            $name = $this->faker->streetAddress;
            $addrLine2 = 'PO BOX: 500';
            $city = $this->faker->city;
            $state = 'NJ';
            $postalCode = $this->faker->postcode;
            $phone = $this->faker->phoneNumber;

            $this->type($name, "locations[$i][name]")
                ->type($name, "locations[$i][address_line_1]")
                ->type($addrLine2, "locations[$i][address_line_2]")
                ->type($city, "locations[$i][city]")
                ->type($state, "locations[$i][state]")
                ->type($postalCode, "locations[$i][postal_code]")
                ->type($phone, "locations[$i][phone]")
                ->press('submit');
        }
    }

    protected function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
        $this->it_creates_practice_lead();
        $this->numberOfLocations = $this->faker->numberBetween(1, 3);
    }

    public function it_creates_practice_lead()
    {
        $firstName = $this->faker->firstName;
        $lastName = $this->faker->lastName;
        $email = $this->faker->email;
        $password = $this->faker->password;

        $this->visit(route('get.onboarding.create.program.lead.user'))
            ->type($firstName, 'firstName')
            ->type($lastName, 'lastName')
            ->type($email, 'email')
            ->type($password, 'password')
            ->press('Create program lead')
            ->seeInDatabase('users', [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $email,
            ])
            ->seePageIs(route('get.onboarding.create.practice'));

        $this->provider = User::whereEmail($email)->first();

        $this->assertTrue(Hash::check($password, $this->provider->password));

        $this->assertTrue($this->provider->hasRole('practice-lead'));
    }

}
