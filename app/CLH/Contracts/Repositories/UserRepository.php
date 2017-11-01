<?php

namespace App\CLH\Contracts\Repositories;

use App\User;
use Symfony\Component\HttpFoundation\ParameterBag;

interface UserRepository
{
    public function createNewUser(
        User $wpUser,
        ParameterBag $params
    );

    public function editUser(
        User $wpUser,
        ParameterBag $params
    );

    public function saveOrUpdateUserMeta(
        User $user,
        ParameterBag $params
    );

    public function updateUserConfig(
        User $wpUser,
        ParameterBag $params
    );

    public function saveOrUpdateRoles(
        User $wpUser,
        ParameterBag $params
    );

    public function saveOrUpdatePrograms(
        User $wpUser,
        ParameterBag $params
    );

    public function adminEmailNotify(
        User $user,
        $recipients
    );

    public function findByRole($role);
}
