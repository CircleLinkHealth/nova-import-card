<?php

namespace App\Services\Admin;

use App\Location;
use App\Role;
use DateTimeZone;
use EllipseSynergie\ApiResponse\Laravel\Response;

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
            'practices'     => $practices,
            'roles'         => $roles,
        ];
    }
}