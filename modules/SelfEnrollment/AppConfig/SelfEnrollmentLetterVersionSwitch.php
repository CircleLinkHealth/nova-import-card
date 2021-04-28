<?php


namespace CircleLinkHealth\SelfEnrollment\AppConfig;

use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetterV2;

class SelfEnrollmentLetterVersionSwitch
{
    const SELF_ENROLLMENT_VERSION_CONFIG_KEY = 'self_enrollment_version';
    const LATEST_VERSION = 'v2';
    const DEFAULT_VERSION = 'v1';

    public static function selfEnrollmentLetterVersion()
    {
        return AppConfig::pull(self::SELF_ENROLLMENT_VERSION_CONFIG_KEY, self::DEFAULT_VERSION);
    }

    public static function latestVersionLoaded()
    {
        return self::selfEnrollmentLetterVersion() === self::LATEST_VERSION;
    }

    public static function loadNewVersionIfModelExists(int $practiceId)
    {
        return self::latestVersionLoaded() && EnrollmentInvitationLetterV2::where('practice_id', $practiceId)
                ->where('is_active', true)->exists();
    }
}