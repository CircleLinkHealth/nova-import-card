<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;

class PatientSupportUser
{
    const PATIENT_SUPPORT_USER_ID_NOVA_KEY = 'patient_support_user_id';

    public static function id()
    {
        return AppConfig::pull(self::PATIENT_SUPPORT_USER_ID_NOVA_KEY, null);
    }
}
