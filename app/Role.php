<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Michalisantoniou6\Cerberus\CerberusRole;

/**
 * App\Role.
 *
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \App\Permission[]|\Illuminate\Database\Eloquent\Collection $perms
 * @property \App\User[]|\Illuminate\Database\Eloquent\Collection $users
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Role extends CerberusRole
{
    const CCM_TIME_ROLES = [
        'care-center',
        'med_assistant',
        'provider',
    ];
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_roles';
    
    /**
     * Get the IDs of Roles from names
     *
     * @param array $roleNames
     *
     * @return array
     */
    public static function getIdsFromNames(array $roleNames = [])
    {
        return \Cache::rememberForever(
            'all_cpm_roles',
            function () {
                return Role::all();
            }
        )->whereIn('name', $roleNames)
                     ->pluck('id')
                     ->all();
    }
}
