<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;
use Illuminate\Support\Str;

class DMDomainForAutoApproval
{
    const FLAG_NAME = 'enable_dm_auto_approval_for_dm_with_domain';

    public static function domains()
    {
        return (new static())->getAndCacheDomains();
    }

    /**
     * Check if feature is enabled for a DirectMail address.
     *
     * Example: testdr@direct.circlelinkhealth.com
     *
     * @param $address
     */
    public static function isEnabledForAddress($address): bool
    {
        foreach ((new static())->getAndCacheDomains() as $str) {
            if (strtolower($address) === strtolower($str)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if feature is enabled for DirectMail domain.
     *
     * Example: @direct.circlelinkhealth.com
     *
     * @param $address
     * @param mixed $domain
     */
    public static function isEnabledForDomain($domain): bool
    {
        foreach ((new static())->getAndCacheDomains() as $str) {
            if (Str::contains($domain, Str::after($str, '@'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if feature is enabled for a Practice.
     *
     * Example: 8@direct.circlelinkhealth.com
     *
     * @param $address
     * @param mixed $practiceId
     */
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
        return AppConfig::pull(self::FLAG_NAME, []);
    }
}
