<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\SAAS\Admin;

use App\ValueObjects\SAAS\Admin\InternalUser;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Customer\Entities\User;

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

    /**
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function storeInternalUser(InternalUser $internalUser)
    {
        if (array_key_exists('id', $internalUser->getUser()) && ! empty($internalUser->getUser()['id'])) {
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
        if (10 == $internalUser->getRole()) {
            $user->nurseInfo()->create([
                'status' => 'active',
            ]);
        }

        return $user->fresh();
    }
}
