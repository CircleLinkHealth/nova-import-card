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
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ses' => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model'   => \CircleLinkHealth\Customer\Entities\User::class,
        'key'     => env('STRIPE_KEY'),
        'secret'  => env('STRIPE_SECRET'),
        'webhook' => [
            'secret'    => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'twilio' => [
        'enabled'          => env('TWILIO_ENABLED', false),
        'sid'              => env('TWILIO_SID', 'somerandomstring'),
        'token'            => env('TWILIO_TOKEN', 'somerandomstring'),
        'from'             => env('TWILIO_FROM', 'somerandomstring'),
        'twiml-app-sid'    => env('TWIML_APP_SID', 'somerandomstring'),
        'allow-conference' => env('TWIML_ALLOW_CONFERENCE', false),
        'allow-recording'  => env('TWIML_ALLOW_RECORDING', false),
        'cpm-caller-url'   => env('CPM_CALLER_URL', ''),
    ],

    'authy' => [
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

    'emr-direct' => [
        'user'                 => env('EMR_DIRECT_USER'),
        'password'             => env('EMR_DIRECT_PASSWORD'),
        'conc-keys-pem-path'   => env('EMR_DIRECT_CONC_KEYS_PEM_PATH'),
        'pass-phrase'          => env('EMR_DIRECT_PASS_PHRASE'),
        'server-cert-pem-path' => env('EMR_DIRECT_SERVER_CERT_PEM_PATH'),
        'mail-server'          => env('EMR_DIRECT_MAIL_SERVER'),
        'port'                 => env('EMR_DIRECT_PORT'),
        'client-cert-filename' => env('EMR_CLIENT_CERT_FILENAME'),
        'server-cert-filename' => env('EMR_SERVER_CERT_FILENAME'),
    ],

    'athena' => [
        'key'     => env('ATHENA_KEY'),
        'secret'  => env('ATHENA_SECRET'),
        'version' => env('ATHENA_VERSION'),
    ],

    'awv' => [
        'url' => env('AWV_URL', ''),
    ],
    
    'phaxio' => [
        'host' => 'https://api.phaxio.com/v2.1/',

        'key'    => env('PHAXIO_KEY', null),
        'secret' => env('PHAXIO_SECRET', null),
    ]
];
