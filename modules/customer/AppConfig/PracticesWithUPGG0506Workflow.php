<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;

class PracticesWithUPGG0506Workflow
{
    /**
     * This flag is for practices that exclusively has requested us to show BHI flag for their patients.
     *
     * See ticket https://circlelinkhealth.atlassian.net/browse/CPM-1784
     */
    const UPG_G0506_WORKFLOW = 'enable_upg_g0506_workflow';

    public static function isEnabledFor($practiceName): bool
    {
        return in_array($practiceName, (new static())->getAndCachePracticeNames());
    }

    /**
     * Returns db field "name" of Practices that have UPG G0506 workflow enabled.
     *
     * See ticket https://circlelinkhealth.atlassian.net/browse/CPM-1904
     *
     * @param null $search
     *
     * @return array
     */
    public static function names()
    {
        return (new static())->getAndCachePracticeNames();
    }

    private function getAndCachePracticeNames()
    {
        return AppConfig::pull(self::UPG_G0506_WORKFLOW, []);
    }
}
