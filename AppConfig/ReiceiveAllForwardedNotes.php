<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;

class ReiceiveAllForwardedNotes
{
    /**
     * This flag is for practices that exclusively has requested us to show BHI flag for their patients.
     *
     * See ticket https://circlelinkhealth.atlassian.net/browse/CPM-1784
     */
    const RECEIVE_ALL_FORWARDED_NOTES = 'receives_all_forwarded_notes';

    public static function emails()
    {
        return \Cache::remember(self::RECEIVE_ALL_FORWARDED_NOTES, 2, function () {
            return AppConfig::where('config_key', '=', self::RECEIVE_ALL_FORWARDED_NOTES)
                ->get()
                ->map(
                    function ($practiceName) {
                        return $practiceName->config_value;
                    }
                )->all();
        });
    }
}
