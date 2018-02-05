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
        if (array_key_exists('id', $internalUser->getUser())) {
            $user = User::find($internalUser->getUser()['id']);
            $user->update($internalUser->getUser());
        } else {
            $user = User::create($internalUser->getUser());
        }

        //If auto_attach_programs, all practices will be attached to the User iduring saved events
        //Otherwise, add all practices below
        if (!$user->auto_attach_programs) {
            $sync = [];

            foreach ($internalUser->getPractices() as $practiceId) {
                $sync[$practiceId] = ['role_id' => $internalUser->getRole()];
            }

            $user->practices()->sync($sync);
        }

        return $user;
    }

    public function getUser($userId)
    {
        $user = User::with(['practices', 'roles'])
                    ->whereId($userId)
                    ->firstOrFail();

        $practices = $user->practices->isNotEmpty()
            ? $user->practices->pluck('id')->all()
            : '';

        $roles = $user->roles->isNotEmpty()
            ? $user->roles->first()->id
            : '';

        return new InternalUser($user, $practices, $roles);
    }
}