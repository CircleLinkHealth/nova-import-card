<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\User;

/**
 * A stand by nurse User is used by ops team to assign all care plans after CarePlan Approval by a CLH admin.
 *
 * Class StandByNurseUser
 */
class StandByNurseUser
{
    const STAND_BY_NURSE_USER_ID_NOVA_KEY = 'stand_by_nurse_user_id';

    public static function id(): ?int
    {
        $value = AppConfig::pull(self::STAND_BY_NURSE_USER_ID_NOVA_KEY, null);

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    public static function user()
    {
        return \Cache::remember(self::STAND_BY_NURSE_USER_ID_NOVA_KEY.'_user_object', 2, function () {
            return User::find(self::id());
        });
    }
}
