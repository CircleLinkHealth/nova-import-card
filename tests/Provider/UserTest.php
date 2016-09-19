<?php

use App\User;
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

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $email = $faker->email;
        $password = $faker->password;

        $this->visit(route('get.create.user'))
            ->type($firstName, 'firstName')
            ->type($lastName, 'lastName')
            ->type($email, 'email')
            ->type($password, 'password')
            ->press('Create account')
            ->seeInDatabase('users', [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'user_email' => $email,
            ])
            ->seePageIs(route('provider.practice.create'));

        $user = User::whereUserEmail($email)->first();

        $this->assertTrue(Hash::check($password, $user->password));
    }
}
