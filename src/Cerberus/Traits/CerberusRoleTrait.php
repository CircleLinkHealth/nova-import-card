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

trait CerberusRoleTrait
{
    protected static $permissionsCache = [];

    /**
     * Attach permission to current role.
     *
     * @param array|object $permission
     *
     * @return void
     */
    public function attachPermission($permission)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            return $this->attachPermissions($permission);
        }

        $this->perms()->attach($permission);
    }

    /**
     * Attach multiple permissions to current role.
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
     * Boot the role model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the role model uses soft deletes.
     *
     * @return bool|void
     */
    public static function bootCerberusRoleTrait()
    {
        static::deleting(function ($role) {
            if ( ! method_exists(Config::get('cerberus.role'), 'bootSoftDeletes')) {
                $role->users()->sync([]);
                $role->perms()->sync([]);
            }

            return true;
        });
    }

    //Big block of caching functionality.
    public function cachedPermissions()
    {
        $cacheKey = $this->getPermissionsCacheKey();
        if ( ! isset(static::$permissionsCache[$cacheKey])) {
            if (Cache::getStore() instanceof TaggableStore) {
                static::$permissionsCache[$cacheKey] = Cache::tags(Config::get('cerberus.permissibles_table'))->remember(
                    $cacheKey,
                    Config::get('cache.ttl', 60),
                    function () {
                        return $this->perms()->get();
                    }
                );
            } else {
                static::$permissionsCache[$cacheKey] = $this->perms()->get();
            }
        }

        return static::$permissionsCache[$cacheKey];
    }

    public function delete(array $options = [])
    {   //soft or hard
        if ( ! parent::delete($options)) {
            return false;
        }
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('cerberus.permissibles_table'))->flush();
        }

        return true;
    }

    /**
     * Detach permission from current role.
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

        $this->perms()->detach($permission);
    }

    /**
     * Detach multiple permissions from current role.
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
     * Checks if the role has a permission by its name.
     *
     * @param array|string $name       permission name or array of permission names
     * @param bool         $requireAll all permissions in the array are required
     *
     * @return bool
     */
    public function hasPermission($name, $requireAll = false)
    {
        if (is_array($name)) {
            foreach ($name as $permissionName) {
                $hasPermission = $this->hasPermission($permissionName);

                if ($hasPermission && ! $requireAll) {
                    return true;
                }
                if ( ! $hasPermission && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the permissions were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the permissions were found.
            // Return the value of $requireAll;
            return $requireAll;
        }
        foreach ($this->cachedPermissions() as $permission) {
            if ($permission->name == $name) {
                return true;
            }
        }

        return false;
    }

    public function perms()
    {
        return $this->morphToMany(Config::get('cerberus.permission'), 'permissible', Config::get('cerberus.permissibles_table'), 'permissible_id', Config::get('cerberus.permission_foreign_key'))
            ->withPivot(['is_active'])
            ->withTimestamps();
    }

    public function restore()
    {   //soft delete undo's
        if ( ! parent::restore()) {
            return false;
        }
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('cerberus.permissibles_table'))->flush();
        }

        return true;
    }

    public function save(array $options = [])
    {   //both inserts and updates
        if ( ! parent::save($options)) {
            return false;
        }
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('cerberus.permissibles_table'))->flush();
        }

        return true;
    }

    /**
     * Save the inputted permissions.
     *
     * @param mixed $inputPermissions
     *
     * @return void
     */
    public function savePermissions($inputPermissions)
    {
        if ( ! empty($inputPermissions)) {
            $this->perms()->sync($inputPermissions);
        } else {
            $this->perms()->detach();
        }

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('cerberus.permissibles_table'))->flush();
        }
    }

    public function setRelation($relation, $value)
    {
        parent::setRelation($relation, $value);
        if ('perms' === $relation) {
            static::$permissionsCache[$this->getPermissionsCacheKey()] = $value;
        }
    }

    public function setRelations(array $relations)
    {
        foreach ($relations as $relation => $value) {
            $this->setRelation($relation, $value);
        }
    }

    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Config::get('cerberus.user'), Config::get('cerberus.role_user_site_table'), Config::get('cerberus.role_foreign_key'), Config::get('cerberus.user_foreign_key'));
    }

    private function getPermissionsCacheKey()
    {
        $rolePrimaryKey = $this->primaryKey;

        return 'cerberus_permissions_for_role_'.$this->$rolePrimaryKey;
    }
}
