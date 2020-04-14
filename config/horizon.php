<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Horizon will be accessible from. If this
    | setting is null, Horizon will reside under the same domain as the
    | application. Otherwise, this value will serve as the subdomain.
    |
    */

    'domain' => null,

    /*
   |--------------------------------------------------------------------------
   | Horizon Path
   |--------------------------------------------------------------------------
   |
   | This is the URI path where Horizon will be accessible from. Feel free
   | to change this path to anything you like. Note that the URI will not
   | affect the paths of its internal API that aren't exposed to users.
   |
   */

    'path' => 'horizon',

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection where Horizon will store the
    | meta information required for it to function. It includes the list
    | of supervisors, failed jobs, job metrics, and other information.
    |
    */

    'use' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing all Horizon data in Redis. You
    | may modify the prefix when you are running multiple installations
    | of Horizon on the same server so that they don't have problems.
    |
    */

    'prefix' => env('HORIZON_PREFIX', 'horizon:'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will get attached onto each Horizon route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    |
    | This option allows you to configure when the LongWaitDetected event
    | will be fired. Every connection / queue combination may have its
    | own, unique threshold (in seconds) before this event is fired.
    |
    */

    'waits' => [
        'redis:default' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long (in minutes) you desire Horizon to
    | persist the recent and failed jobs. Typically, recent jobs are kept
    | for one hour while all failed jobs are stored for an entire week.
    |
    */

    'trim' => [
        'recent' => 60,
        'failed' => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue worker settings used by your application
    | in all environments. These supervisors and settings handle all your
    | queued jobs and will be provisioned by Horizon during deployment.
    |
    */

    'environments' => [
        'local' => [
            'supervisor-1' => [
                'connection'   => 'redis',
                'queue'        => ['high', 'default', 'low'],
                'balance'      => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 1,
                'tries'        => 3,
                'timeout'      => 300,
            ],
        ],
        'review' => [
            'supervisor-1' => [
                'connection'   => 'redis',
                'queue'        => ['high', 'default', 'low'],
                'balance'      => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 1,
                'tries'        => 3,
                'timeout'      => 300,
            ],
        ],
        'staging' => [
            'supervisor-1' => [
                'connection'   => 'redis',
                'queue'        => ['high', 'default', 'low'],
                'balance'      => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 2,
                'tries'        => 3,
                'timeout'      => 300,
            ],
        ],
        'production_v3' => [
            'supervisor-1' => [
                'connection'   => 'redis',
                'queue'        => ['high', 'default', 'low'],
                'balance'      => 'auto',
                'minProcesses' => 5,
                'maxProcesses' => 10,
                'tries'        => 3,
                'timeout'      => 900,
            ],
        ],
    ],
];
