<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;

class Reminders
{
    const DISABLE = 'practice_disable_self_enrolment_reminders';
    
    public static function areEnabledFor($practiceName): bool
    {
        return in_array($practiceName, (new static())->getAndCachePracticeNames());
    }
    
    public static function names()
    {
        return (new static())->getAndCachePracticeNames();
    }
    
    private function getAndCachePracticeNames()
    {
        return AppConfig::pull(self::DISABLE, []);
    }
}
