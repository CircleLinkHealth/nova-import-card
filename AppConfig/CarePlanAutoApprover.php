<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;

/**
 * Automatically QA approve careplans on behalf of this CLH Adminn.
 *
 * Class CarePlanAutoApprover
 */
class CarePlanAutoApprover
{
    const CARE_PLAN_AUTO_APPROVER_USER_ID_NOVA_KEY = 'careplan_auto_approver_user_id';

    public static function id()
    {
        return AppConfig::pull(self::CARE_PLAN_AUTO_APPROVER_USER_ID_NOVA_KEY, null);
    }
}
