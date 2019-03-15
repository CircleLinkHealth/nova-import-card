<?php

return [
    'enable_crash_reporting' => env('ENABLE_RAYGUN_CRASH_REPORTING', false),
    
    'enable_real_user_monitoring' => env('ENABLE_RAYGUN_REAL_USER_MONITORING', false),
    
    'apiKey'    => env('RAYGUN_API_KEY', 'yourapikey'),
    
    /**
     * WARNING: PHP `exec()` must be enabled for this. IF it's not enabled, it default to fals at runtime.
     */
    'async'     => env('ENABLE_RAYGUN_CRASH_REPORTING_ASYNC_MODE', false),
    
    'debugMode' => env('ENABLE_RAYGUN_CRASH_REPORTING_DEBUG_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Logger Notify Level
    |--------------------------------------------------------------------------
    |
    | This sets the level at which a logged message will trigger a notification
    | to Bugsnag.  By default this level will be 'debug'.
    |
    | Must be one of the Psr\Log\LogLevel levels from the Psr specification.
    |
    */

    'logger_notify_level' => env('RAYGUN_CRASH_REPORTING_LOGGER_LEVEL', 'debug'),
];
