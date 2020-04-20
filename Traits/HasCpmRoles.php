<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use App\Constants;
use Illuminate\Support\Facades\Cache;

trait HasCpmRoles
{
    protected $permsCacheOnObj = [];
    /**
     * This variable is used to cache roles on an object.
     *
     * @var array
     */
    protected $rolesCacheOnObj = [];

    public function clearObjectCache()
    {
        $this->rolesCacheOnObj = $this->permsCacheOnObj = [];
    }

    public function getCpmRolesCacheKey()
    {
        return "cpm_roles:user_id:$this->id";
    }

    public function hasPermission($name, $operator = 'OR')
    {
        if (is_string($name)) {
            return $this->getCachedPermission($name);
        }

        $permissions = [];

        foreach ((array) $name as $permissionName) {
            $hasRole = $this->getCachedPermission($permissionName);

            if ($hasRole && 'OR' == $operator) {
                return true;
            }

            $permissions[] = filter_var($hasRole, FILTER_VALIDATE_BOOLEAN);
        }

        return in_array(true, $permissions);
    }

    public function hasRole($name, $operator = 'OR')
    {
        $roles = [];

        if (is_string($name)) {
            return $this->getCachedRole($name);
        }

        foreach ((array) $name as $roleName) {
            $hasRole = $this->getCachedRole($roleName);

            if ($hasRole && 'OR' == $operator) {
                return true;
            }

            $roles[] = filter_var($hasRole, FILTER_VALIDATE_BOOLEAN);
        }

        return in_array(true, $roles);
    }

    /**
     * Returns whether the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->getCachedRole('administrator');
    }

    /**
     * Returns whether the user is an administrator.
     *
     * @param bool $includeViewOnly
     */
    public function isCareAmbassador($includeViewOnly = true): bool
    {
        $arr = ['care-ambassador'];
        if ($includeViewOnly) {
            $arr[] = 'care-ambassador-view-only';
        }

        return $this->getCachedRole($arr);
    }

    /**
     * Returns whether the user is a Care Coach (AKA Care Center).
     * A Care Coach can be employed from CLH ['care-center']
     * or not ['care-center-external'].
     */
    public function isCareCoach(): bool
    {
        return $this->getCachedRole(['care-center', 'care-center-external']);
    }

    /**
     * Returns whether the user is an administrator.
     */
    public function isEhrReportWriter(): bool
    {
        return $this->getCachedRole('ehr-report-writer');
    }

    /**
     * Returns whether the user is a participant.
     */
    public function isParticipant(): bool
    {
        return $this->getCachedRole('participant');
    }

    public function isPracticeStaff(): bool
    {
        return $this->getCachedRole(Constants::PRACTICE_STAFF_ROLE_NAMES);
    }

    /**
     * Returns whether the user is an administrator.
     */
    public function isProvider(): bool
    {
        return $this->getCachedRole('provider');
    }

    /**
     * Returns whether the user is an administrator.
     */
    public function isSaasAdmin(): bool
    {
        return $this->getCachedRole('saas-admin');
    }

    /**
     * Returns whether the user is a Software Only user.
     */
    public function isSoftwareOnly(): bool
    {
        return $this->getCachedRole('software-only');
    }

    private function getCachedPermission($key)
    {
        if ( ! $this->id) {
            return false;
        }

        $key = (array) $key;

        foreach ($key as $permName) {
            if (is_null($this->permsCacheOnObj[$permName] ?? null)) {
                $cacheDriver = config('cache.default');

                if ('redis' !== $cacheDriver) {
                    $this->permsCacheOnObj[$permName] = Cache::remember(
                        "user_id:$this->id:_perm_$permName",
                        2,
                        function () use ($permName) {
                            return $this->cerberusHasPermission($permName);
                        }
                    );
                } else {
                    $hget = \RedisManager::hget($this->getCpmRolesCacheKey(), $permName);
                    if ( ! $hget) {
                        \RedisManager::hset($this->getCpmRolesCacheKey(), $permName, $this->cerberusHasPermission($permName) ? 'true' : 'false');
                        $hget = \RedisManager::hget($this->getCpmRolesCacheKey(), $permName);
                    }

                    $this->permsCacheOnObj[$permName] = filter_var($hget, FILTER_VALIDATE_BOOLEAN);
                }
            }
        }

        foreach ($key as $permName) {
            $outcome = array_key_exists($permName, $this->permsCacheOnObj) && (bool) $this->permsCacheOnObj[$permName];

            if (true === $outcome) {
                return true;
            }
        }

        return false;
    }

    private function getCachedRole($key)
    {
        if ( ! $this->id) {
            return false;
        }

        $key = (array) $key;

        foreach ($key as $roleName) {
            if (is_null($this->rolesCacheOnObj[$roleName] ?? null)) {
                $cacheDriver = config('cache.default');

                if ('redis' !== $cacheDriver) {
                    $this->rolesCacheOnObj[$roleName] = $this->cerberusHasRole($roleName);
                } else {
                    $hget = \RedisManager::hget($this->getCpmRolesCacheKey(), $roleName);
                    if ( ! $hget) {
                        \RedisManager::hset($this->getCpmRolesCacheKey(), $roleName, $this->cerberusHasRole($roleName) ? 'true' : 'false');
                        $hget = \RedisManager::hget($this->getCpmRolesCacheKey(), $roleName);
                    }

                    $this->rolesCacheOnObj[$roleName] = filter_var($hget, FILTER_VALIDATE_BOOLEAN);
                }
            }
        }

        foreach ($key as $roleName) {
            $outcome = array_key_exists($roleName, $this->rolesCacheOnObj) && (bool) $this->rolesCacheOnObj[$roleName];

            if (true === $outcome) {
                return true;
            }
        }

        return false;
    }
}
