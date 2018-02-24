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
        $user = User::updateOrCreate($internalUser->getUser());

        $sync = [];

        foreach ($internalUser->getPractices() as $practiceId) {
            $sync[$practiceId] = ['role_id' => $internalUser->getRole()];
        }

        $user->practices()->sync($sync);

        return $user;
    }

    public function getUser($userId)
    {
        $user = User::with(['practices', 'roles'])
                    ->whereId($userId)
                    ->first();


        return new InternalUser($user, $user->practices->isNotEmpty() ? $user->practices->pluck('id')->all() : '', $user->roles->isNotEmpty() ? $user->roles->first()->id : '');
    }
}