<?php

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
    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model'  => App\User::class,
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'twilio' => [
        'sid'              => env('TWILIO_SID'),
        'token'            => env('TWILIO_TOKEN'),
        'from'             => env('TWILIO_FROM'),
        'twiml-app-sid'    => env('TWIML_APP_SID'),
        'allow-conference' => env('TWIML_ALLOW_CONFERENCE', false),
    ],

    'authy' => [
        'api_key' => env('AUTHY_API_KEY'),
    ],

    'ws' => [
        'server-url' => env('WS_SERVER_URL'),
        'url'        => env('WS_URL'),
        'root'       => env('WS_ROOT'),
    ],

    'time-tracker' => [
        'override-timeout' => in_array(env('APP_ENV'), ['local', 'staging'])
            ? 'true'
            : 'false',
    ],

    'ccd-parser' => [
        'base-uri' => env('CCD_PARSER_BASE_URI', 'https://circlelink-ccd-parser.medstack.net'),
    ],

    'ccda' => [
        'dropbox-path' => env('CCDA_DROPBOX_PATH'),
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
    ],

    'athena' => [
        'key'     => env('ATHENA_KEY'),
        'secret'  => env('ATHENA_SECRET'),
        'version' => env('ATHENA_VERSION'),
    ],
];
