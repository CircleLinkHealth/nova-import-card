<?php namespace Michalisantoniou6\Entrust\Traits;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Entrust
 */

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

trait EntrustUserTrait
{
    //Big block of caching functionality.
    /**
     * Boot the user model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the user model uses soft deletes.
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            if ( ! method_exists(Config::get('auth.model'), 'bootSoftDeletes')) {
                $user->roles()->sync([]);
            }

            return true;
        });
    }

    public function save(array $options = [])
    {   //both inserts and updates
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('entrust.role_user_site_table'))->flush();
        }

        return parent::save($options);
    }

    public function delete(array $options = [])
    {   //soft or hard
        parent::delete($options);
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('entrust.role_user_site_table'))->flush();
        }
    }

    public function restore()
    {   //soft delete undo's
        parent::restore();
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('entrust.role_user_site_table'))->flush();
        }
    }

    /**
     * Checks role(s) and permission(s).
     *
     * @param string|array $roles Array of roles or comma separated string
     * @param string|array $permissions Array of permissions or comma separated string.
     * @param array $options validate_all (true|false) or return_type (boolean|array|both)
     *
     * @param $site
     *
     * @return array|bool
     */
    public function ability($roles, $permissions, $options = [], $site)
    {
        // Convert string to array if that's what is passed in.
        if ( ! is_array($roles)) {
            $roles = explode(',', $roles);
        }
        if ( ! is_array($permissions)) {
            $permissions = explode(',', $permissions);
        }

        // Set up default values and validate options.
        if ( ! isset($options['validate_all'])) {
            $options['validate_all'] = false;
        } else {
            if ($options['validate_all'] !== true && $options['validate_all'] !== false) {
                throw new InvalidArgumentException();
            }
        }
        if ( ! isset($options['return_type'])) {
            $options['return_type'] = 'boolean';
        } else {
            if ($options['return_type'] != 'boolean' &&
                $options['return_type'] != 'array' &&
                $options['return_type'] != 'both') {
                throw new InvalidArgumentException();
            }
        }

        // Loop through roles and permissions and check each.
        $checkedRoles       = [];
        $checkedPermissions = [];
        foreach ($roles as $role) {
            $checkedRoles[$role] = $this->hasRole($role, false, $site);
        }
        foreach ($permissions as $permission) {
            $checkedPermissions[$permission] = $this->can($permission);
        }

        // If validate all and there is a false in either
        // Check that if validate all, then there should not be any false.
        // Check that if not validate all, there must be at least one true.
        if (($options['validate_all'] && ! (in_array(false, $checkedRoles) || in_array(false, $checkedPermissions))) ||
            ( ! $options['validate_all'] && (in_array(true, $checkedRoles) || in_array(true, $checkedPermissions)))) {
            $validateAll = true;
        } else {
            $validateAll = false;
        }

        // Return based on option
        if ($options['return_type'] == 'boolean') {
            return $validateAll;
        } elseif ($options['return_type'] == 'array') {
            return ['roles' => $checkedRoles, 'permissions' => $checkedPermissions];
        } else {
            return [$validateAll, ['roles' => $checkedRoles, 'permissions' => $checkedPermissions]];
        }

    }

    /**
     * Checks if the user has a role by its name.
     *
     * @param string|array $name Role name or array of role names.
     * @param bool $requireAll All roles in the array are required.
     * @param $site
     *
     * @return bool
     */
    public function hasRole($name, $requireAll = false, $site)
    {
        if (is_array($name)) {
            foreach ($name as $roleName) {
                $hasRole = $this->hasRole($roleName, false, $site);

                if ($hasRole && ! $requireAll) {
                    return true;
                } elseif ( ! $hasRole && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the roles were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the roles were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->cachedRoles() as $role) {
                if ($role->name == $name) {
                    return true;
                }
            }
        }

        return false;
    }

    public function cachedRoles()
    {
        $userPrimaryKey = $this->primaryKey;
        $cacheKey       = 'entrust_roles_for_user_' . $this->$userPrimaryKey;
        if (Cache::getStore() instanceof TaggableStore) {
            return Cache::tags(Config::get('entrust.role_user_site_table'))->remember($cacheKey,
                Config::get('cache.ttl'), function () {
                    return $this->roles()->get();
                });
        } else {
            return $this->roles()->get();
        }
    }

    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('entrust.role'), Config::get('entrust.role_user_site_table'),
            Config::get('entrust.user_foreign_key'), Config::get('entrust.role_foreign_key'))
                    ->withPivot(Config::get('entrust.site_foreign_key'));
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function can($permission, $requireAll = false)
    {
        if (is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->can($permName);

                if ($hasPerm && ! $requireAll) {
                    return true;
                } elseif ( ! $hasPerm && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the perms were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the perms were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->cachedRoles() as $role) {
                // Validate against the Permission table
                foreach ($role->cachedPermissions() as $perm) {
                    if (str_is($permission, $perm->name)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Attach multiple roles to a user
     *
     * @param mixed $roles
     * @param $site
     */
    public function attachRoles($roles, $site)
    {
        foreach ($roles as $role) {
            $this->attachRole($role, $site);
        }
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     * @param mixed $site
     */
    public function attachRole($role, $site)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->attach($role, [
            Config::get('entrust.site_foreign_key') => $site,
        ]);
    }

    /**
     * Detach multiple roles from a user
     *
     * @param mixed $roles
     * @param $site
     */
    public function detachRoles($roles = null, $site)
    {
        if ( ! $roles) {
            $roles = $this->roles()->where(Config::get('entrust.site_foreign_key'), '=', $site)->get();
        }

        foreach ($roles as $role) {
            $this->detachRole($role, $site);
        }
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     * @param $site
     *
     * @return
     */
    public function detachRole($role, $site)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        return DB::table(Config::get('entrust.role_user_site_table'))->where([
            [Config::get('entrust.role_foreign_key'), '=', $role],
            [Config::get('entrust.site_foreign_key'), '=', $site],
        ])->delete();
    }

}
