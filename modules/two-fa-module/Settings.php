<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwoFA;

use CircleLinkHealth\Core\Entities\AppConfig;

class Settings
{
    public static function appConfigKey()
    {
        return 'is_two_fa_enabled_'.config('app.unique_env_name');
    }

    public static function isTwoFAEnabled(): bool
    {
        return filter_var(AppConfig::pull(self::appConfigKey()), FILTER_VALIDATE_BOOLEAN);
    }
}
