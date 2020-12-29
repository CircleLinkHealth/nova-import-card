<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Twofa;

use CircleLinkHealth\Core\Entities\AppConfig;

class Settings
{
    public static function appConfigKey()
    {
        return 'is_two_fa_enabled_'.app()->environment();
    }

    public static function isTwoFAEnabled(): bool
    {
        return boolval(AppConfig::pull(self::appConfigKey()));
    }
}
