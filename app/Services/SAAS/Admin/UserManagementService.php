<?php

namespace App\Services\SAAS\Admin;

use App\Role;
use App\User;
use App\ValueObjects\SAAS\Admin\InternalUser;

class UserManagementService
{
    /**
     * @return array
     */
    public function getDataForCreateUserPage()
    {
        $roles = Role::whereIn('name', ['saas-admin', 'care-center'])
                     ->get()
                     ->pluck('display_name', 'id');

        $practices = auth()->user()
            ->practices
            ->pluck('display_name', 'id');

        return [
            'practices' => $practices,
            'roles'     => $roles,
        ];
    }

    /**
     * @param $args
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function storeInternalUser(InternalUser $internalUser)
    {
        $user = User::create($internalUser->getUser());

        foreach ($internalUser->getPractices() as $practiceId) {
            $user->attachRoleForSite($internalUser->getRole(), $practiceId);
        }

        return $user;
    }

    public function getUser($userId)
    {
        $user = User::with(['practices', 'roles'])
                    ->whereId($userId)
                    ->first();


        return new InternalUser($user, $user->practices->pluck('id')->all(), $user->roles->first()->id);
    }
}