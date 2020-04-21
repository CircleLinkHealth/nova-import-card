<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;

class PracticesRequiringSpecialBhiConsent
{
    /**
     * This flag is for practices that exclusively has requested us to show BHI flag for their patients.
     *
     * See ticket https://circlelinkhealth.atlassian.net/browse/CPM-1784
     */
    const PRACTICE_REQUIRES_SPECIAL_BHI_CONSENT_NOVA_KEY = 'practice_requires_special_bhi_consent';

    /**
     * Returns db field "name" of Practices that exclusively has requested us to show BHI flag for their patients.
     *
     * See ticket https://circlelinkhealth.atlassian.net/browse/CPM-1784
     *
     * @return array
     */
    public static function names()
    {
        return \Cache::remember(self::PRACTICE_REQUIRES_SPECIAL_BHI_CONSENT_NOVA_KEY, 2, function () {
            return AppConfig::where('config_key', '=', self::PRACTICE_REQUIRES_SPECIAL_BHI_CONSENT_NOVA_KEY)
                ->get()
                ->map(
                    function ($practiceName) {
                        return $practiceName->config_value;
                    }
                )->all();
        });
    }
}
