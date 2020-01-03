<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for every one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_DRIVER', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver'      => 'database',
            'table'       => 'jobs',
            'queue'       => 'default',
            'retry_after' => 300,
        ],

        'beanstalkd' => [
            'driver'      => 'beanstalkd',
            'host'        => 'localhost',
            'queue'       => 'default',
            'retry_after' => 300,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key'    => 'your-public-key',
            'secret' => 'your-secret-key',
            'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
            'queue'  => 'your-queue-name',
            'region' => 'us-east-1',
        ],

        'redis' => [
            'driver'      => 'redis',
            'connection'  => 'default',
            'queue'       => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for'   => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table'    => 'failed_jobs',
    ],
];
