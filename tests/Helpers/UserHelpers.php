<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 14/11/2016
 * Time: 4:01 PM
 */

namespace Tests\Helpers;

use App\CLH\Facades\StringManipulation;
use App\CLH\Repositories\UserRepository;
use App\Practice;
use App\Role;
use App\User;
use Faker\Factory;
use Symfony\Component\HttpFoundation\ParameterBag;


trait UserHelpers
{
    /**
     * @param int $practiceId
     * @param string $roleName
     *
     * @return User
     */
    public function createUser(
        $practiceId = 9,
        $roleName = 'provider'
    ) : User
    {
        $faker = Factory::create();

        $firstName = $faker->firstName;
        $lastName = $faker->lastName;
        $email = $faker->email;
        $workPhone = StringManipulation::formatPhoneNumber($faker->phoneNumber);

        $roles = [
            Role::whereName($roleName)->first()->id,
        ];

        $bag = new ParameterBag([
            'email'             => $email,
            'password'          => 'password',
            'display_name'      => "$firstName $lastName",
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'username'          => $faker->userName,
            'program_id'        => $practiceId,
            //id=9 is testdrive
            'address'           => $faker->streetAddress,
            'address2'          => '',
            'city'              => $faker->city,
            'state'             => 'AL',
            'zip'               => '12345',
            'is_auto_generated' => true,
            'roles'             => $roles,
            'timezone'          => 'America/New_York',

            //provider Info
            'prefix'            => 'Dr',
            'qualification'     => 'MD',
            'npi_number'        => 1234567890,
            'specialty'         => 'Unit Tester',

            //phones
            'home_phone_number' => $workPhone,
        ]);

        //create a user
        $user = (new UserRepository())->createNewUser(new User(), $bag);

        $locations = Practice::find($practiceId)->locations
            ->pluck('id')
            ->all();

        $user->locations()->sync($locations);

        foreach ($locations as $locId) {
            $this->seeInDatabase('location_user', [
                'location_id' => $locId,
                'user_id'     => $user->id,
            ]);
        }

        //check that it was created
        $this->seeInDatabase('users', ['email' => $email]);

        //check that the roles were created
        foreach ($roles as $role) {
            $this->seeInDatabase('lv_role_user', [
                'user_id' => $user->id,
                'role_id' => $role,
            ]);
        }

        return $user;
    }

    public function userLogin(User $user)
    {
        $this->visit('/auth/login')
            ->see('CarePlanManager')
            ->type($user->email, 'email')
            ->type('password', 'password')
            ->press('Log In')
            ->seePageIs('/manage-patients/dashboard');

        //By default PHPUnit fails the test if the output buffer wasn't closed.
        //So we're adding this to make the test work.
        ob_end_clean();
    }

}