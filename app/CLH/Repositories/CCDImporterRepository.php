<?php

namespace App\CLH\Repositories;


use App\CLH\Repositories\WpUserRepository;
use App\Role;
use App\WpUser;
use Symfony\Component\HttpFoundation\ParameterBag;

class CCDImporterRepository
{
    /**
     * Creates a user with random credentials
     * Used to attach XML CCDs to a Patient
     *
     * @return WpUser
     */
    public function createRandomUser()
    {
        $role = Role::whereName('patient')->first();

        $newUserId = str_random(20);

        $bag = new ParameterBag([
            'user_email' => $newUserId . '@careplanmanager.com',
            'user_pass' => 'whatToPutHere',
            'user_nicename' => $newUserId,
            'primary_blog' => '7', //@todo: thoughts on how we get this?
            'roles' => [$role->id],
        ]);

        return (new WpUserRepository())->createNewUser(new WpUser(), $bag);
    }

}