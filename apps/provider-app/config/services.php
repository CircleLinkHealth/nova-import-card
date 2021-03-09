<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'authy' => [
        'api_url' => env('AUTHY_API_URL', 'https://api.authy.com'),
        'api_key' => env('AUTHY_API_KEY'),
    ],

    'ws' => [
        'server-url'    => env('WS_SERVER_URL'),
        'url'           => env('WS_URL'),
        'url-fail-over' => env('WS_URL_FAIL_OVER'),
        'root'          => env('WS_ROOT'),
    ],

    'time-tracker' => [
        'override-timeout' => in_array(env('APP_ENV'), ['local', 'staging'])
            ? 'true'
            : 'false',
    ],

    'no-call-mode' => [
        'env' => ! in_array(env('APP_ENV'), ['local', 'staging']),
    ],

    'athena' => [
        'v1' => [
            'key'         => env('ATHENA_V1_KEY'),
            'secret'      => env('ATHENA_V1_SECRET'),
            'version'     => env('ATHENA_V1_VERSION', 'preview1'),
            'practice_id' => env('ATHENA_V1_CLH_PRACTICE_ID', '195900'),
        ],
        'v2' => [
            'key'         => env('ATHENA_V2_KEY'),
            'secret'      => env('ATHENA_V2_SECRET'),
            'version'     => env('ATHENA_V2_VERSION', 'preview1'),
            'practice_id' => env('ATHENA_V2_CLH_PRACTICE_ID', '195900'),
        ],
        'active_version' => env('ATHENA_ACTIVE_VERSION', 'v1'),
    ],

    'awv' => [
        'url'        => env('AWV_URL', ''),
        'report_url' => env('AWV_URL', '').env('AWV_REPORT_URI', ''),
    ],

    'serverless-pdf-generator' => [
        'api-url'         => env('SERVERLESS_PDF_GENERATOR_API_URL', ''),
        'api-key'         => env('SERVERLESS_PDF_GENERATOR_API_KEY', ''),
        'default-options' => [
            'format' => 'Letter',
            'scale'  => 0.8,
            'margin' => [
                'top'    => '1cm',
                'bottom' => '1cm',
                'left'   => '1cm',
                'right'  => '1cm',
            ],
        ],
        'mail-vendor-envelope-options' => [
            'scale'  => 1.0,
            'margin' => [
                'top'    => '4mm',
                'bottom' => '25mm',
                'left'   => '25mm',
                'right'  => '0.5mm',
            ],
        ],
    ],

    'tester' => [
        'email'     => 'nektariosx01@gmail.com',
        'phone'     => '+35799018718',
        'phone_two' => '+35799952761',
        'email_two' => 'kountouris7@gmail.com',
    ],

    'intercom' => [
        'intercom_app_id' => env('INTERCOM_APP_ID', ''),
    ],
];
