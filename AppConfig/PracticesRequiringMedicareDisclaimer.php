<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/20/19
 * Time: 11:46 AM
 */

namespace CircleLinkHealth\Customer\AppConfig;


use App\AppConfig;

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
        return \Cache::remember(self::PRACTICE_REQUIRES_MEDICARE_DISCLAIMER_NOVA_KEY, 2, function () {
            return AppConfig::where('config_key', '=', self::PRACTICE_REQUIRES_MEDICARE_DISCLAIMER_NOVA_KEY)
                            ->get()
                            ->map(
                                function ($practiceName) {
                                    return $practiceName->config_value;
                                }
                            )->all();
        });
    }
    
    public static function shouldShowMedicareDisclaimer($practiceName)
    {
        return in_array($practiceName, self::names());
    }
}