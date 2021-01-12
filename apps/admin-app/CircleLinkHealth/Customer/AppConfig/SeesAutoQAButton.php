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
        $values = AppConfig::pull(self::SEES_AUTO_QA_BUTTON, []);

        return in_array($userId, $values);
    }
}
