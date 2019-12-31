<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/16/19
 * Time: 5:36 AM
 */

namespace CircleLinkHealth\Customer\AppConfig;


use App\AppConfig;

class PatientSupportUser
{
    const PATIENT_SUPPORT_USER_ID_NOVA_KEY = 'patient_support_user_id';
    
    public static function id() {
        return \Cache::remember(self::PATIENT_SUPPORT_USER_ID_NOVA_KEY, 2, function () {
            return AppConfig::where('config_key', '=', self::PATIENT_SUPPORT_USER_ID_NOVA_KEY)
                            ->firstOrFail()->config_value;
        });
    }
}