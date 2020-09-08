<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    /*
   |--------------------------------------------------------------------------
   | API Key
   |--------------------------------------------------------------------------
   |
   | You can find your API key on your Raygun dashboard, under "Appplication Settings".
   |
   | This api key points the Raygun notifier to the project in your account
   | which should receive your application's uncaught exceptions.
   |
   */
    'api_key' => env('RAYGUN_API_KEY', 'yourapikey'),

    /*
    |--------------------------------------------------------------------------
    | Enable Crash Reporting
    |--------------------------------------------------------------------------
    |
    | Use Crash Reporting feature in this application.
    |
    | This will send exceptions and logs to Raygun, and they will appear under Crash Reporting.
    | Defaults to false.
    |
    */
    'enable_crash_reporting' => env('ENABLE_RAYGUN_CRASH_REPORTING', false),

    /*
    |--------------------------------------------------------------------------
    | Logger Notify Level
    |--------------------------------------------------------------------------
    |
    | This sets the level at which a logged message will trigger a notification
    | to Raygun.
    |
    | Must be one of the Psr\Log\LogLevel levels from the Psr specification.
    | Defaults to 'debug'.
    |
    */
    'logger_notify_level' => env('RAYGUN_CRASH_REPORTING_LOGGER_LEVEL', 'warning'),

    /*
    |--------------------------------------------------------------------------
    | Enable Crash Reporting Async Mode
    |--------------------------------------------------------------------------
    |
    | Send data to Crash Reporting asynchronously
    |
    | WARNING: PHP `exec()` must be enabled for this. IF it's not enabled, it default to fals at runtime.
    | Defaults to false.
    |
    */
    'async' => env('ENABLE_RAYGUN_CRASH_REPORTING_ASYNC_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Enable Raygun Debug Mode
    |--------------------------------------------------------------------------
    |
    |
    |
    |
    | Defaults to false.
    |
    */
    'debugMode' => env('ENABLE_RAYGUN_CRASH_REPORTING_DEBUG_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Enable Real User Monitoring
    |--------------------------------------------------------------------------
    |
    | Use Real User Monitoring feature in this application.
    |
    | This will send monitoring data to Raygun, and it will appear under Real User Monitoring.
    | Defaults to false.
    |
    */
    'enable_real_user_monitoring' => env('ENABLE_RAYGUN_REAL_USER_MONITORING', false),

    /*
    |--------------------------------------------------------------------------
    | Enable Real User Monitoring Pulse
    |--------------------------------------------------------------------------
    |
    | Enable pulse in Real User Monitoring feature in this application.
    |
    |
    | Defaults to false.
    |
    */
    'enable_real_user_monitoring_pulse' => env('ENABLE_RAYGUN_REAL_USER_MONITORING_PULSE', false),

    /*
   |--------------------------------------------------------------------------
   | Log contents of XHR calls
   |--------------------------------------------------------------------------
   |
   | Enable logging contents of XHR calls in this application.
   |
   |
   | Defaults to false.
   |
   */
    'log_contents_of_xhr_calls' => env('RAYGUN_LOG_CONTENTS_OF_XHR_CALLS', true),

    /*
    |--------------------------------------------------------------------------
    | Raygun User
    |--------------------------------------------------------------------------
    |
    | A User to be reported to Real User Monitoring
    |
    |
    |
    |
    */
    'raygun_user' => CircleLinkHealth\Raygun\Entities\RaygunUser::class,

    /*
    |--------------------------------------------------------------------------
    | App Version Variable in .env
    |--------------------------------------------------------------------------
    |
    | Where to get the APP_VERSION from
    |
    |
    | Defaults to ''.
    |
    */
    'app_version' => env('APP_VERSION', ''),
];
