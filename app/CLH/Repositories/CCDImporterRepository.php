<?php

namespace App\CLH\Repositories;


use App\CLH\Repositories\WpUserRepository;
use App\Role;
use App\WpUser;
use Symfony\Component\HttpFoundation\ParameterBag;

class CCDImporterRepository
{
    public function createUser()
    {
        $role = Role::whereName('patient')->first();

        $newUserId = str_random(20);

        $bag = new ParameterBag([
            'user_email' => $newUserId . '@careplanmanager.com',
            'user_pass' => 'whatToPutHere',
            'user_nicename' => $newUserId,
            'primary_blog' => '7',
            'roles' => [$role->id],
        ]);

        return (new WpUserRepository())->createNewUser(new WpUser(), $bag);
    }

}