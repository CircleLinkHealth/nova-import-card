<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Config;

use CircleLinkHealth\Core\Entities\AppConfig;

class NurseCcmPlusConfig
{
    /**
     * This flag toggles ccm plus algorithm for specific nurses (comma separated).
     */
    const NURSE_CCM_PLUS_ALT_ALGO_ENABLED_FOR_USER_IDS = 'nurse_ccm_plus_alt_algo_enabled_for_user_ids';

    /**
     * This flag toggles ccm plus algorithm for all nurses.
     */
    const NURSE_CCM_PLUS_ENABLED_FOR_ALL = 'nurse_ccm_plus_enabled_for_all';

    public static function altAlgoEnabledForUserIds(): array
    {
        return \Cache::remember(self::NURSE_CCM_PLUS_ALT_ALGO_ENABLED_FOR_USER_IDS, 2, function () {
            $val = AppConfig::where('config_key', '=', self::NURSE_CCM_PLUS_ALT_ALGO_ENABLED_FOR_USER_IDS)
                ->firstOrFail()->config_value;
            if (empty($val)) {
                return [];
            }

            return explode(',', $val);
        });
    }

    public static function enabledForAll(): bool
    {
        return \Cache::remember(self::NURSE_CCM_PLUS_ENABLED_FOR_ALL, 2, function () {
            $val = AppConfig::where('config_key', '=', self::NURSE_CCM_PLUS_ENABLED_FOR_ALL)
                ->firstOrFail()->config_value;

            return filter_var($val, FILTER_VALIDATE_BOOLEAN);
        });
    }
}
