<?php

namespace App\CLH\Repositories;

use App\Role;
use App\User;
use Symfony\Component\HttpFoundation\ParameterBag;

class CCDImporterRepository
{
    /**
     * Creates a user with random credentials
     * Used to attach XML CCDs to a Patient
     *
     * @return User
     */
    public function createRandomUser($blogId, $email = '', $fullName = '')
    {
        $role = Role::whereName('participant')->first();

        if (empty($role)) throw new \Exception('User role not found.', 500);

        $newUserId = str_random(20);

        $user_email = empty($email)
            ? $newUserId . '@careplanmanager.com'
            : $email;

        $user_login = empty($email)
            ? $newUserId
            : $email;

        //user_nicename, display_name
        $user_nicename = empty($fullName)
            ? ''
            : ucwords(strtolower($fullName));

        $bag = new ParameterBag([
            'user_email' => $user_email,
            'user_pass' => 'whatToPutHere',
            'user_nicename' => $user_nicename,
            'display_name' => $user_nicename,
            'user_login' => $user_login,
            'program_id' => $blogId,
            'roles' => [$role->id],
        ]);

        return (new UserRepository())->createNewUser(new User(), $bag);
    }

}