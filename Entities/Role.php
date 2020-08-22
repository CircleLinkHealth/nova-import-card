<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Laravel\Scout\Searchable;
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
 * @method   static                                                                                    \Illuminate\Database\Eloquent\Builder|\App\Role whereCreatedAt($value)
 * @method   static                                                                                    \Illuminate\Database\Eloquent\Builder|\App\Role whereDescription($value)
 * @method   static                                                                                    \Illuminate\Database\Eloquent\Builder|\App\Role whereDisplayName($value)
 * @method   static                                                                                    \Illuminate\Database\Eloquent\Builder|\App\Role whereId($value)
 * @method   static                                                                                    \Illuminate\Database\Eloquent\Builder|\App\Role whereName($value)
 * @method   static                                                                                    \Illuminate\Database\Eloquent\Builder|\App\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method   static   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Role newModelQuery()
 * @method   static   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Role newQuery()
 * @method   static   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Role query()
 * @property int|null $perms_count
 * @property int|null $users_count
 */
class Role extends CerberusRole
{
    use Searchable;
    const ALL_CPM_ROLES_CACHE_KEY = 'all_cpm_roles';

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
    private const CACHE_ROLES_MINUTES = 3;

    protected $fillable = [
        'name',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lv_roles';

    protected $with = ['perms'];

    public static function allRoles()
    {
        return \Cache::remember(
            self::ALL_CPM_ROLES_CACHE_KEY,
            Role::CACHE_ROLES_MINUTES,
            function () {
                return Role::all();
            }
        );
    }

    public static function byName(string $name)
    {
        return \Cache::remember("cached_role_$name", 2, function () use ($name) {
            return Role::where('name', $name)->firstOrFail();
        });
    }

    /**
     * Get the IDs of Roles from names.
     *
     * @return array
     */
    public static function getIdsFromNames(array $roleNames = [])
    {
        return self::allRoles()
            ->whereIn('name', $roleNames)
            ->pluck('id')
            ->all();
    }

    /**
     * Get the value used to index the model.
     *
     * @return mixed
     */
    public function getScoutKey()
    {
        return $this->id;
    }

    /**
     * Get Scout index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'roles_index';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'name'         => $this->name,
            'display_name' => $this->display_name,
        ];
    }
}
