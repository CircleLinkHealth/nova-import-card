<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 13/02/2017
 * Time: 11:29 PM
 */

namespace App\Services;

use App\PhoneNumber;
use App\Role;

class OnboardingService
{
    public function getExistingStaff($primaryPractice)
    {
        //Get the users that were as clinical emergency contacts from the locations page
        $existingUsers = $primaryPractice->users->map(function ($user) {
            return [
                'id'                 => $user->id,
                'email'              => $user->email,
                'last_name'          => $user->last_name,
                'first_name'         => $user->first_name,
                'phone_number'       => $user->phoneNumbers->first()['number'] ?? '',
                'phone_type'         => array_search($user->phoneNumbers->first()['type'],
                        PhoneNumber::getTypes()) ?? '',
                'isComplete'         => false,
                'validated'          => false,
                'grandAdminRights'   => false,
                'sendBillingReports' => false,
                'errorCount'         => 0,
                'role_id'            => $user->roles->first()['id'] ?? 0,
            ];
        });

        $locations = $primaryPractice->locations->map(function ($loc) {
            return [
                'id'   => $loc->id,
                'name' => $loc->name,
            ];
        });

        $locationIds = $primaryPractice->locations->map(function ($loc) {
            return $loc->id;
        });

        //get the relevant roles
        $roles = Role::whereIn('name', [
            'med_assistant',
            'office_admin',
            'practice-lead',
            'provider',
            'registered-nurse',
            'specialist',
        ])->get([
            'id',
            'display_name',
        ])
            ->sortBy('display_name');

        \JavaScript::put([
            'existingUsers' => $existingUsers,
            'locations'     => $locations,
            'locationIds'   => $locationIds,
            'phoneTypes'    => PhoneNumber::getTypes(),
            'roles'         => $roles->all(),
            //this will help us get role names on the views: rolesMap[id]
            'rolesMap'      => $roles->keyBy('id')->all(),
        ]);
    }

}