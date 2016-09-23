<?php

use App\User;
use Faker\Factory;

class OnboardingTest extends TestCase
{
    protected $faker;
    
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
                'user_email' => $email,
            ])
            ->seePageIs(route('get.onboarding.create.practice'));

        $this->provider = User::whereUserEmail($email)->first();

        $this->assertTrue(Hash::check($password, $this->provider->password));

        $this->assertTrue($this->provider->hasRole('program-lead'));
    }

    public function createPractice()
    {
        $name = $this->faker->company;
        $description = $this->faker->text();
        $locations = $this->faker->numberBetween(1, 15);

        $this->actingAs($this->provider)
            ->visit(route('get.onboarding.create.practice'))
            ->visit(route('get.onboarding.create.practice'))
            ->type($name, 'name')
            ->type($description, 'description')
            ->type($description, 'locations')
            ->press('create-practice');
    }

}
