<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Michalisantoniou6\Cerberus\Traits;

use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

trait CerberusSiteUserTrait
{
    private $permissionsCache = [];
    private $rolesCache       = [];

    /**
     * Checks role(s) and permission(s).
     *
     * @param array|string $roles       Array of roles or comma separated string
     * @param array|string $permissions array of permissions or comma separated string
     * @param $site
     * @param array $options validate_all (true|false) or return_type (boolean|array|both)
     *
     * @return array|bool
     */
    public function abilityForSite($roles, $permissions, $site, $options = [])
    {
        $this->validateSite($site);

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
            $checkedRoles[$role] = $this->hasRoleForSite($role, false, $site);
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
        if ('boolean' == $options['return_type']) {
            return $validateAll;
        }
        if ('array' == $options['return_type']) {
            return ['roles' => $checkedRoles, 'permissions' => $checkedPermissions];
        }

        return [$validateAll, ['roles' => $checkedRoles, 'permissions' => $checkedPermissions]];
    }

    /**
     * Attach permission to current user.
     *
     * @param array|object $permission
     * @param mixed        $isActive
     *
     * @return void
     */
    public function attachPermission($permission, $isActive = 1)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            $this->attachPermissions($permission);

            return;
        }

        if ($this->perms()->find($permission)) {
            $this->perms()->updateExistingPivot($permission, ['is_active' => $isActive]);
        } else {
            $this->perms()->attach($permission, ['is_active' => $isActive]);
        }

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('cerberus.permissibles_table'))->flush();
        }
    }

    /**
     * Attach multiple permissions to current user.
     *
     * @param mixed $permissions
     *
     * @return void
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     * @param mixed $site
     */
    public function attachRoleForSite($role, $site)
    {
        $this->validateSite($site);

        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            foreach ($role as $key => $roleId) {
                $this->attachRoleForSite($roleId, $site);
                unset($role[$key]);
            }
        }

        if (empty($role)) {
            return;
        }

        if ( ! is_numeric($role)) {
            throw new \Exception('Not a valid role id.');
        }

        $this->roles()->attach($role, [
            Config::get('cerberus.site_foreign_key') => $site,
        ]);
    }

    /**
     * Attach multiple roles to a user.
     *
     * @param mixed $roles
     * @param $site
     */
    public function attachRolesForSite($roles, $site)
    {
        $this->validateSite($site);

        foreach ($roles as $role) {
            $this->attachRoleForSite($role, $site);
        }
    }

    public function cachedPermissions()
    {
        $cacheKey = 'cerberus_permissions_for_user_'.$this->id;
        if ( ! isset($this->permissionsCache[$cacheKey])) {
            if (Cache::getStore() instanceof TaggableStore) {
                $this->permissionsCache[$cacheKey] = Cache::tags(Config::get('cerberus.permissibles_table'))->remember(
                    $cacheKey,
                    Config::get('cache.ttl', 60),
                    function () {
                        return $this->perms()->get();
                    }
                );
            } else {
                $this->permissionsCache[$cacheKey] = $this->perms()->get();
            }
        }

        return $this->permissionsCache[$cacheKey];
    }

    public function cachedRoles()
    {
        $cacheKey = 'cerberus_roles_for_user_'.$this->id;
        if ( ! isset($this->rolesCache[$cacheKey])) {
            if (Cache::getStore() instanceof TaggableStore) {
                $this->rolesCache[$cacheKey] = Cache::tags(Config::get('cerberus.role_user_site_table'))->remember(
                    $cacheKey,
                    Config::get('cache.ttl'),
                    function () {
                        return $this->roles()->get();
                    }
                );
            } else {
                $this->rolesCache[$cacheKey] = $this->roles()->get();
            }
        }

        return $this->rolesCache[$cacheKey];
    }

    public function clearRolesCache()
    {
        $keys = [
            'cerberus_roles_for_user_'.$this->id,
            'cerberus_permissions_for_user_'.$this->id,
        ];
        if (\Cache::getStore() instanceof TaggableStore) {
            $store = \Cache::tags(Config::get('cerberus.role_user_site_table'));
        } else {
            $store = \Cache::getStore();
        }

        foreach ($keys as $key) {
            unset($this->rolesCache[$key], $this->permissionsCache[$key]);

            $store->forget($key);
            Cache::forget($key);
        }

        $this->unsetRelation('roles');
    }

    /**
     * Detach permission from current user.
     *
     * @param array|object $permission
     *
     * @return void
     */
    public function detachPermission($permission)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            return $this->detachPermissions($permission);
        }

        if ($this->perms()->find($permission)) {
            $this->perms()->updateExistingPivot($permission, ['is_active' => 0]);
            if (Cache::getStore() instanceof TaggableStore) {
                Cache::tags(Config::get('cerberus.permissibles_table'))->flush();
            }
        } else {
            foreach ($this->cachedRoles() as $role) {
                // Validate against the Permission table
                foreach ($role->cachedPermissions() as $perm) {
                    if ($perm->id == $permission) {
                        $this->perms()->attach($permission, ['is_active' => 0]);
                        if (Cache::getStore() instanceof TaggableStore) {
                            Cache::tags(Config::get('cerberus.permissibles_table'))->flush();
                        }
                    }
                }
            }
        }
    }

    /**
     * Detach multiple permissions from current user.
     *
     * @param mixed $permissions
     *
     * @return void
     */
    public function detachPermissions($permissions = null)
    {
        if ( ! $permissions) {
            $permissions = $this->perms()->get();
        }

        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
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
    public function detachRoleForSite($role, $site)
    {
        $this->validateSite($site);

        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            foreach ($role as $key => $roleId) {
                $this->detachRoleForSite($roleId, $site);
                unset($role[$key]);
            }
        }

        return DB::table(Config::get('cerberus.role_user_site_table'))->where([
            [Config::get('cerberus.role_foreign_key'), '=', $role],
            [Config::get('cerberus.site_foreign_key'), '=', $site],
            [Config::get('cerberus.user_foreign_key'), '=', $this->getKey()],
        ])->delete();
    }

    /**
     * Detach multiple roles from a user.
     *
     * @param mixed $roles
     * @param $site
     */
    public function detachRolesForSite($roles = null, $site)
    {
        $this->validateSite($site);

        if ( ! $roles) {
            $roles = $this->roles()->where(Config::get('cerberus.site_foreign_key'), '=', $site)->get();
        }

        foreach ($roles as $role) {
            $this->detachRoleForSite($role, $site);
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
        foreach ($this->cachedPermissions() as $perm) {
            //first check if the User has this permission directly related and active
            //if it's not active it means it has been removed from the default permissions of a role for this User.
            if (Str::is($permission, $perm->name)) {
                return $perm->pivot->is_active
                    ? true
                    : false;
            }
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
     * Check if user has a permission by its name.
     *
     * @param array|string $permission permission string or array of permissions
     * @param bool         $requireAll all permissions in the array are required
     * @param mixed        $site
     *
     * @return bool
     */
    public function hasPermissionForSite($permission, $site, $requireAll = false)
    {
        if (is_a(Model::class, $site)) {
            $site = $site->getKey();
        }

        if (is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->can($permName);

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
            if ($role->pivot->{Config::get('cerberus.site_foreign_key')} != $site) {
                continue;
            }
            foreach ($this->cachedPermissions() as $perm) {
                //first check if the User has this permission directly related and active
                //if it's not active it means it has been removed from the default permissions of a role for this User.
                if (Str::is($permission, $perm->name)) {
                    return $perm->pivot->is_active
                        ? true
                        : false;
                }
            }
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
     * Checks if the user has a role by its name for a site.
     *
     * @param array|string $name       role name or array of role names
     * @param bool         $requireAll all roles in the array are required
     * @param $site
     *
     * @return bool
     */
    public function hasRoleForSite($name, $site, $requireAll = false)
    {
        if (is_array($name)) {
            foreach ($name as $roleName) {
                $hasRole = $this->hasRoleForSite($roleName, $site, $requireAll);

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
            if ($role->name == $name && ($role->pivot->{Config::get('cerberus.site_foreign_key')} == $site)) {
                return true;
            }
        }

        return false;
    }

    public function perms()
    {
        return $this->morphToMany(
            Config::get('cerberus.permission'),
            'permissible',
            Config::get('cerberus.permissibles_table'),
            'permissible_id',
            Config::get('cerberus.permission_foreign_key')
        )
            ->withPivot(['is_active'])
            ->withTimestamps();
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
        )
            ->withPivot(Config::get('cerberus.site_foreign_key'));
    }

    /**
     * Checks whether $site is required and $site is empty.
     *
     * @param $site
     *
     * @throws \Exception
     * @return bool
     */
    public function validateSite($site)
    {
        if ( ! $site) {
            throw new \Exception('The site is required.');
        }

        return true;
    }
}
