<?php

use Faker\Factory;

class UserTest extends TestCase
{
    /**
     * Check that the form to create a User is there.
     */
    public function testSeeCreateUserForm()
    {
        $this->visit(route('get.create.user'))
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

        $this->visit(route('get.create.user'))
            ->type($faker->firstName, 'firstName')
            ->type($faker->lastName, 'lastName')
            ->type($faker->email, 'email')
            ->type($faker->password, 'password')
            ->press('Create account')
            ->seePageIs(route('get.provider.dashboard'));
    }
}
