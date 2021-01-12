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

    'cpm-app' => [
        'url' => env('CPM_APP_URL', null),
    ],

    'twilio' => [
        'enabled'          => env('TWILIO_ENABLED', false),
        'account_sid'      => env('TWILIO_SID', 'somerandomstring'),
        'auth_token'       => env('TWILIO_TOKEN', 'somerandomstring'),
        'from'             => env('TWILIO_FROM', 'somerandomstring'),
        'twiml-app-sid'    => env('TWIML_APP_SID', 'somerandomstring'),
        'allow-conference' => env('TWIML_ALLOW_CONFERENCE', false),
        'allow-recording'  => env('TWIML_ALLOW_RECORDING', false),
        'cpm-caller-url'   => env('CPM_CALLER_URL', ''),
    ],

    'serverless-pdf-generator' => [
        'api-url'         => env('SERVERLESS_PDF_GENERATOR_API_URL'),
        'api-key'         => env('SERVERLESS_PDF_GENERATOR_API_KEY'),
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
            'scale'  => 0.7,
            'margin' => [
                'top'    => '12mm',
                'bottom' => '15mm',
                'left'   => '25mm',
                'right'  => '0.75mm',
            ],
        ],
    ],
];
