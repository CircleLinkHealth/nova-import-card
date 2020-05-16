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

    'emr-direct' => [
        'user'                 => env('EMR_DIRECT_USER'),
        'test_user'            => env('EMR_DIRECT_TEST_USER'),
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
        'url'        => env('AWV_URL', ''),
        'report_url' => env('AWV_URL', '').env('AWV_REPORT_URI', ''),
    ],

    'phaxio' => [
        'host' => 'https://api.phaxio.com/v2.1/',

        'key'    => env('PHAXIO_KEY', null),
        'secret' => env('PHAXIO_SECRET', null),
    ],

    'tester' => [
        'email'     => 'nektariosx01@gmail.com',
        'phone'     => '+35799018718',
        'phone_two' => '+35799952761',
        'email_two' => 'kountouris7@gmail.com',
    ],
];
