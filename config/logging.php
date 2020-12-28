<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', env('APP_LOG', 'daily')),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver'            => 'stack',
            'channels'          => ['stderr'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path'   => storage_path('logs/laravel-'.php_sapi_name().'.log'),
            'level'  => env('APP_LOG_LEVEL', \Monolog\Logger::DEBUG),
        ],

        'daily' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/laravel-'.php_sapi_name().'.log'),
            'level'  => env('APP_LOG_LEVEL', \Monolog\Logger::DEBUG),
            'days'   => 14,
        ],

        'database' => [
            'driver'           => 'custom',
            'via'              => danielme85\LaravelLogToDB\LogToDbHandler::class,
            'model'            => \CircleLinkHealth\SharedModels\Entities\DatabaseLog::class, //Your own optional custom model
            'level'            => env('APP_LOG_LEVEL', 'debug'),
            'name'             => 'database',
            'connection'       => 'default',
            'collection'       => 'log',
            'detailed'         => true,
            'queue'            => true,
            'queue_name'       => '',
            'queue_connection' => '',
            'max_records'      => false,
            'max_hours'        => false,
            'processors'       => [
                //Monolog\Processor\HostnameProcessor::class
                // ..
            ],
        ],

        'null' => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],

        'slack' => [
            'driver'   => 'slack',
            'url'      => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji'    => ':boom:',
            'level'    => 'critical',
        ],

        'papertrail' => [
            'driver'       => 'monolog',
            'level'        => env('APP_LOG_LEVEL', \Monolog\Logger::DEBUG),
            'handler'      => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver'    => 'monolog',
            'handler'   => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with'      => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level'  => env('APP_LOG_LEVEL', \Monolog\Logger::DEBUG),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level'  => env('APP_LOG_LEVEL', \Monolog\Logger::DEBUG),
        ],

        'sentry' => [
            'driver' => 'sentry',
            // The minimum monolog logging level at which this handler will be triggered
            // For example: `\Monolog\Logger::ERROR`
            'level' => \Monolog\Logger::ERROR,
            // Whether the messages that are handled can bubble up the stack or not
            'bubble' => true,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],
];
