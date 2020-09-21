<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Core\Entities\AppConfig;

if ( ! function_exists('isProductionEnv')) {
    /**
     * Returns whether or not this is a Production server, ie. used by real users.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    function isProductionEnv()
    {
        return config('core.is_production_env');
    }
}

if ( ! function_exists('isQueueWorkerEnv')) {
    /**
     * Returns whether or not this server runs jobs from the queue.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    function isQueueWorkerEnv()
    {
        return config('core.is_queue_worker_env');
    }
}

if ( ! function_exists('isUnitTestingEnv')) {
    /**
     * Returns whether or not the test suite is running.
     *
     * @return bool|string
     */
    function isUnitTestingEnv()
    {
        return app()->environment(['testing']);
    }
}

if ( ! function_exists('upg0506IsEnabled')) {
    /**
     * Key: upg0506_is_enabled
     * Default: false.
     */
    function upg0506IsEnabled(): bool
    {
        $key = 'upg0506_is_enabled';
        $val = AppConfig::pull($key, null);
        if (null === $val) {
            return 'true' === AppConfig::set($key, false);
        }
        
        return 'true' === $val;
    }
}

if ( ! function_exists('getEhrReportWritersFolderUrl')) {
    function getEhrReportWritersFolderUrl()
    {
        //this is to make local environments faster for devs
        //comment out this if section to use the feature
        if (app()->environment('local')) {
            return null;
        }
        
        $key = 'ehr_report_writers_folder_url';
        
        return \Cache::remember($key, 2, function () use ($key) {
            return AppConfig::pull($key, null);
        });

//        Commenting out due to Heroku migration
//        $dir = getGoogleDirectoryByName('ehr-data-from-report-writers');
//
//        if ( ! $dir) {
//            return null;
//        }
//
//        return "https://drive.google.com/drive/folders/{$dir['path']}";
    }
}

if ( ! function_exists('isSelfEnrollmentTestModeEnabled')) {
    function isSelfEnrollmentTestModeEnabled(): bool
    {
        return filter_var(AppConfig::pull('testing_enroll_sms', true), FILTER_VALIDATE_BOOLEAN);
    }
}

if ( ! function_exists('isAllowedToSee2FA')) {
    function isAllowedToSee2FA(User $user = null)
    {
        $twoFaEnabled = (bool) config('auth.two_fa_enabled');
        if ( ! $twoFaEnabled) {
            return false;
        }
        
        if ( ! $user) {
            $user = auth()->user();
        }
        
        if ( ! $user || $user->isParticipant()) {
            return false;
        }
        
        return $user->isAdmin() || isTwoFaEnabledForPractice($user->program_id);
    }
}

if ( ! function_exists('isTwoFaEnabledForPractice')) {
    /**
     * Key: two_fa_enabled_practices
     * Default: false.
     *
     * @param mixed $practiceId
     */
    function isTwoFaEnabledForPractice($practiceId): bool
    {
        $key = 'two_fa_enabled_practices';
        $val = AppConfig::pull($key, null);
        if (null === $val) {
            AppConfig::set($key, '');
            
            $twoFaEnabledPractices = [];
        } else {
            $twoFaEnabledPractices = explode(',', $val);
        }
        
        return in_array($practiceId, $twoFaEnabledPractices);
    }
}