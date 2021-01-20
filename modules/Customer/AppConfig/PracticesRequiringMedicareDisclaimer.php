<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;

class PracticesRequiringMedicareDisclaimer
{
    /**
     * This nova key is for practices that have exclusively requested us to show a Medicare disclaimer on their careplans.
     *
     * See ticket https://circlelinkhealth.atlassian.net/browse/CPM-1578
     */
    const PRACTICE_REQUIRES_MEDICARE_DISCLAIMER_NOVA_KEY = 'practice_requires_medicare_disclaimer';

    /**
     * Returns db field "name" of Practices that exclusively has requested us to show a Medicare disclaimer on their careplans.
     *
     * See ticket https://circlelinkhealth.atlassian.net/browse/CPM-1578
     *
     * @return array
     */
    public static function names()
    {
        return AppConfig::pull(self::PRACTICE_REQUIRES_MEDICARE_DISCLAIMER_NOVA_KEY, []);
    }

    public static function shouldShowMedicareDisclaimer($practiceName)
    {
        return in_array($practiceName, self::names());
    }
}
