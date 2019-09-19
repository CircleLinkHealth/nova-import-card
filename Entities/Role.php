<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Michalisantoniou6\Cerberus\CerberusRole;

/**
 * CircleLinkHealth\Customer\Entities\Role.
 *
 * @property int                                                                                       $id
 * @property string                                                                                    $name
 * @property string|null                                                                               $display_name
 * @property string|null                                                                               $description
 * @property \Carbon\Carbon                                                                            $created_at
 * @property \Carbon\Carbon                                                                            $updated_at
 * @property \CircleLinkHealth\Customer\Entities\Permission[]|\Illuminate\Database\Eloquent\Collection $perms
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection       $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Role query()
 * @property-read int|null $perms_count
 * @property-read int|null $users_count
 */
class Role extends CerberusRole
{
    const CCM_TIME_ROLES = [
        'care-center',
        'care-center-external',
        'med_assistant',
        'provider',
    ];

    /**
     * Cache roles for 24 Hours.
     *
     * @var int
     */
    private const CACHE_ROLES_MINUTES = 1440;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_roles';

    /**
     * Get the IDs of Roles from names.
     *
     * @param array $roleNames
     *
     * @return array
     */
    public static function getIdsFromNames(array $roleNames = [])
    {
        return \Cache::remember(
            'all_cpm_roles',
            Role::CACHE_ROLES_MINUTES,
            function () {
                return Role::all();
            }
        )
            ->whereIn('name', $roleNames)
            ->pluck('id')
            ->all();
    }
}
