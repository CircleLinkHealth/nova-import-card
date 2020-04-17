<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/16/19
 * Time: 5:36 AM
 */

namespace CircleLinkHealth\Customer\AppConfig;


use CircleLinkHealth\Core\Entities\AppConfig;

class StandByNurseUser
{
    const STAND_BY_NURSE_USER_ID_NOVA_KEY = 'stand_by_nurse_user_id';
    
    public static function id() {
        return \Cache::remember(self::STAND_BY_NURSE_USER_ID_NOVA_KEY, 2, function () {
            return optional(AppConfig::where('config_key', '=', self::STAND_BY_NURSE_USER_ID_NOVA_KEY)
                                     ->first())->config_value;
        });
    }
}