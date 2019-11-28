<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
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
        'recent' => 1440,
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
                'connection' => 'redis',
                'queue'      => ['high', 'default', 'low'],
                'balance'    => 'auto',
                'processes'  => 8,
                'tries'      => 1,
                'timeout'    => 300,
            ],
        ],
        'production' => [
        ],
        'staging' => [
            'supervisor-1' => [
                'connection'    => 'redis',
                'queue'         => ['default', 'low', 'demanding'],
                'balance'       => 'auto',
                'min-processes' => 1,
                'max-processes' => 2,
                'tries'         => 1,
                'timeout'       => 60,
            ],
            'supervisor-2' => [
                'connection'    => 'redis',
                'queue'         => ['high'],
                'balance'       => 'simple',
                'min-processes' => 2,
                'max-processes' => 7,
                'tries'         => 1,
                'timeout'       => 30,
            ],
        ],
        'worker' => [
            'supervisor-1' => [
                'connection'    => 'redis',
                'queue'         => ['default', 'low', 'demanding'],
                'balance'       => 'auto',
                'min-processes' => 1,
                'max-processes' => 7,
                'tries'         => 1,
                'timeout'       => 60,
            ],
            'supervisor-2' => [
                'connection'    => 'redis',
                'queue'         => ['high'],
                'balance'       => 'simple',
                'min-processes' => 2,
                'max-processes' => 7,
                'tries'         => 1,
                'timeout'       => 30,
            ],
        ],
    ],
];
