<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\AppConfig;

use Illuminate\Support\Str;
use CircleLinkHealth\Core\Entities\AppConfig;

class DMDomainForAutoApproval
{
    const FLAG_NAME = 'enable_dm_auto_approval_for_dm_with_domain';

    public static function domains()
    {
        return (new static())->getAndCacheDomains();
    }

    public static function isEnabledForDomain($domain): bool
    {
        foreach ((new static())->getAndCacheDomains() as $str) {
            if (Str::contains($domain, Str::after($str, '@'))) {
                return true;
            }
        }

        return false;
    }

    public static function isEnabledForPractice($practiceId): bool
    {
        foreach ((new static())->getAndCacheDomains() as $str) {
            if (Str::startsWith($str, "$practiceId@")) {
                return true;
            }
        }

        return false;
    }

    private function getAndCacheDomains()
    {
        return \Cache::remember(self::FLAG_NAME, 2, function () {
            return AppConfig::where('config_key', '=', self::FLAG_NAME)
                ->get()
                ->map(
                                function ($config) {
                                    return $config->config_value;
                                }
                            )->all();
        });
    }
}
