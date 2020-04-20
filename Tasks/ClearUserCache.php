<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Tasks;

use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class ClearUserCache
{
    public static function roles(User $user)
    {
        $keys = [
            'cerberus_roles_for_user_'.$user->id,
            'cerberus_permissions_for_user_'.$user->id,
            $user->getCpmRolesCacheKey(),
        ];
        if (\Cache::getStore() instanceof TaggableStore) {
            $store = \Cache::tags(Config::get('cerberus.role_user_site_table'));
        } else {
            $store = \Cache::getStore();
        }

        foreach ($keys as $key) {
            $store->forget($key);
            Cache::forget($key);
        }

        $cacheDriver = config('cache.default');

        if ('redis' === $cacheDriver) {
            \RedisManager::del($user->getCpmRolesCacheKey());
        }

        $user->clearObjectCache();
        $user->unsetRelation('roles');
    }
}
