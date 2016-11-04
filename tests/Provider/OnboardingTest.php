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

    public function __construct()
    {
        parent::__construct();

        $this->faker = Factory::create();
    }

    /**
     * Check that the form to create a User is there.
     */
    public function testSeeCreateUserForm()
    {
        $this->visit(route('get.onboarding.create.program.lead.user'))
            ->see('firstName')
            ->see('lastName')
            ->see('email')
            ->see('password');
    }

    public function testOnboardingProcess()
    {
        $this->createProgramLead();
        $this->createPractice();
//        $this->createLocations($this->numberOfLocations);
    }

    public function createProgramLead()
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

        $this->assertTrue($this->provider->hasRole('program-lead'));
    }

    public function createPractice()
    {
        $name = $this->faker->company;
        $description = $this->faker->text();
        $this->numberOfLocations = $this->faker->numberBetween(1, 15);

        $this->actingAs($this->provider)
            ->visit(route('get.onboarding.create.practice'))
            ->type($name, 'name')
            ->type($description, 'description')
            ->type($this->numberOfLocations, 'numberOfLocations')
            ->press('create-practice');

        $this->seeInDatabase('wp_blogs', [
            'name'         => str_slug($name),
            'display_name' => $name,
            'description'  => $description,
            'user_id'      => auth()->user()->ID,
        ]);
    }

    public function createLocations($number)
    {
        $this->actingAs($this->provider)
            ->visit(route('get.onboarding.create.locations', [
                'numberOflocations' => $number,
            ]));


        for ($i = 0; $i <= $number; $i++) {
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

}
