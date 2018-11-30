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
        $rolesChoices = ['saas-admin', 'care-center'];

        if (auth()->user()->isAdmin()) {
            $rolesChoices[] = 'administrator';
        }

        $roles = Role::whereIn('name', $rolesChoices)
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
        if (array_key_exists('id', $internalUser->getUser()) && !empty($internalUser->getUser()['id'])) {
            $user = User::find($internalUser->getUser()['id']);
            $user->update($internalUser->getUser());
        } else {
            $user = User::create($internalUser->getUser());
        }

        $sync = [];

        //We are doing this even if the user has auto_attach_programs
        //So that the user will have a role
        foreach ($internalUser->getPractices() as $practiceId) {
            $sync[$practiceId] = ['role_id' => $internalUser->getRole()];
        }

        $user->practices()->sync($sync);

        //Save so that the saved event will run and replace
        $user->save();

        //if role = care-center
        if ($internalUser->getRole() == 10) {
            $user->nurseInfo()->create([
                'status' => 'active',
            ]);
        }

        return $user->fresh();
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