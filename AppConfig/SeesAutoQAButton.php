<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;

/**
 * A stand by nurse User is used by ops team to assign all care plans after CarePlan Approval by a CLH admin.
 *
 * Class StandByNurseUser
 */
class SeesAutoQAButton
{
    const SEES_AUTO_QA_BUTTON = 'sees_auto_qa_button';

    public static function userId(int $userId)
    {
        return \Cache::remember(self::SEES_AUTO_QA_BUTTON, 2, function () use ($userId) {
            return optional(AppConfig::where('config_key', '=', self::SEES_AUTO_QA_BUTTON)->where('config_value', '=', $userId)->first())->config_value;
        });
    }
}
