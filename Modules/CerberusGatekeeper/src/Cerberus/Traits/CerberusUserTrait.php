<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Michalisantoniou6\Cerberus\Traits;

/*
 * This file is part of Cerberus,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Michalisantoniou6\Cerberus
 */

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use InvalidArgumentException;

trait CerberusUserTrait
{
    /**
     * Checks role(s) and permission(s).
     *
     * @param array|string $roles       Array of roles or comma separated string
     * @param array|string $permissions array of permissions or comma separated string
     * @param array        $options     validate_all (true|false) or return_type (boolean|array|both)
     *
     * @throws \InvalidArgumentException
     *
     * @return array|bool
     */
    public function ability($roles, $permissions, $options = [])
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
            if (true !== $options['validate_all'] && false !== $options['validate_all']) {
                throw new InvalidArgumentException();
            }
        }
        if ( ! isset($options['return_type'])) {
            $options['return_type'] = 'boolean';
        } else {
            if ('boolean' != $options['return_type'] &&
                'array' != $options['return_type'] &&
                'both' != $options['return_type']) {
                throw new InvalidArgumentException();
            }
        }
        // Loop through roles and permissions and check each.
        $checkedRoles       = [];
        $checkedPermissions = [];
        foreach ($roles as $role) {
            $checkedRoles[$role] = $this->hasRole($role);
        }
        foreach ($permissions as $permission) {
            $checkedPermissions[$permission] = $this->hasPermission($permission);
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
        if ('boolean' == $options['return_type']) {
            return $validateAll;
        }
        if ('array' == $options['return_type']) {
            return ['roles' => $checkedRoles, 'permissions' => $checkedPermissions];
        }

        return [$validateAll, ['roles' => $checkedRoles, 'permissions' => $checkedPermissions]];
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     */
    public function attachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }
        if (is_array($role)) {
            $role = $role['id'];
        }
        $this->roles()->attach($role);
    }

    /**
     * Attach multiple roles to a user.
     *
     * @param mixed $roles
     */
    public function attachRoles($roles)
    {
        foreach ($roles as $role) {
            $this->attachRole($role);
        }
    }

    public function cachedRoles()
    {
        $userPrimaryKey = $this->primaryKey;
        $cacheKey       = 'cerberus_roles_for_user_'.$this->$userPrimaryKey;
        if (Cache::getStore() instanceof TaggableStore) {
            return Cache::tags(Config::get('cerberus.role_user_site_table'))->remember(
                $cacheKey,
                Config::get('cache.ttl'),
                function () {
                    return $this->roles()->get();
                }
            );
        } else {
            return $this->roles()->get();
        }
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     */
    public function detachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }
        if (is_array($role)) {
            $role = $role['id'];
        }
        $this->roles()->detach($role);
    }

    /**
     * Detach multiple roles from a user.
     *
     * @param mixed $roles
     */
    public function detachRoles($roles = null)
    {
        if ( ! $roles) {
            $roles = $this->roles()->get();
        }
        foreach ($roles as $role) {
            $this->detachRole($role);
        }
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param array|string $permission permission string or array of permissions
     * @param bool         $requireAll all permissions in the array are required
     *
     * @return bool
     */
    public function hasPermission($permission, $requireAll = false)
    {
        if (is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->hasPermission($permName);

                if ($hasPerm && ! $requireAll) {
                    return true;
                }
                if ( ! $hasPerm && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the perms were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the perms were found.
            // Return the value of $requireAll;
            return $requireAll;
        }
        foreach ($this->cachedRoles() as $role) {
            // Validate against the Permission table
            foreach ($role->cachedPermissions() as $perm) {
                if (Str::is($permission, $perm->name)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if the user has a role by its name.
     *
     * @param array|string $name       role name or array of role names
     * @param bool         $requireAll all roles in the array are required
     *
     * @return bool
     */
    public function hasRole($name, $requireAll = false)
    {
        if (is_array($name)) {
            foreach ($name as $roleName) {
                $hasRole = $this->hasRole($roleName, false);

                if ($hasRole && ! $requireAll) {
                    return true;
                }
                if ( ! $hasRole && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the roles were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the roles were found.
            // Return the value of $requireAll;
            return $requireAll;
        }
        foreach ($this->cachedRoles() as $role) {
            if ($role->name == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            Config::get('cerberus.role'),
            Config::get('cerberus.role_user_site_table'),
            Config::get('cerberus.user_foreign_key'),
            Config::get('cerberus.role_foreign_key')
        );
    }
}
