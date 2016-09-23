<?php

use App\User;
use Faker\Factory;

class OnboardingTest extends TestCase
{
    /**
     * @var User $provider
     */
    protected $provider;

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

    /**
     * Check that we can fill and submit the form.
     * @param Factory $faker
     */
    public function testFillAndSubmitCreateUserForm()
    {
        $faker = Factory::create();

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $email = $faker->email;
        $password = $faker->password;

        $this->visit(route('get.onboarding.create.program.lead.user'))
            ->type($firstName, 'firstName')
            ->type($lastName, 'lastName')
            ->type($email, 'email')
            ->type($password, 'password')
            ->press('Create program lead')
            ->seeInDatabase('users', [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'user_email' => $email,
            ])
            ->seePageIs(route('get.onboarding.create.practice'));

        $this->provider = User::whereUserEmail($email)->first();

        $this->assertTrue(Hash::check($password, $this->provider->password));

        $this->assertTrue($this->provider->hasRole('program-lead'));


        $name = $faker->company;
        $description = $faker->text();
        $locations = $faker->numberBetween(1, 15);

        $this->actingAs($this->provider)
            ->visit(route('get.onboarding.create.practice'))
            ->visit(route('get.onboarding.create.practice'))
            ->type($name, 'name')
            ->type($description, 'description')
            ->type($description, 'locations')
            ->press('create-practice');
    }

}
