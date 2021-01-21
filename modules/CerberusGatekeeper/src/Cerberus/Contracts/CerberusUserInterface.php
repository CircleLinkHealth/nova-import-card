<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Michalisantoniou6\Cerberus\Contracts;

/**
 * This file is part of Cerberus,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 */
interface CerberusUserInterface
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
    public function ability($roles, $permissions, $options = []);

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     */
    public function attachRole($role);

    /**
     * Attach multiple roles to a user.
     *
     * @param mixed $roles
     */
    public function attachRoles($roles);

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     */
    public function detachRole($role);

    /**
     * Detach multiple roles from a user.
     *
     * @param mixed $roles
     */
    public function detachRoles($roles);

    /**
     * Check if user has a permission by its name.
     *
     * @param array|string $permission permission string or array of permissions
     * @param bool         $requireAll all permissions in the array are required
     *
     * @return bool
     */
    public function hasPermission($permission, $requireAll = false);

    /**
     * Checks if the user has a role by its name.
     *
     * @param array|string $name       role name or array of role names
     * @param bool         $requireAll all roles in the array are required
     *
     * @return bool
     */
    public function hasRole($name, $requireAll = false);

    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles();
}
