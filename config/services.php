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

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ses' => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model'   => CircleLinkHealth\Customer\Entities\User::class,
        'key'     => env('STRIPE_KEY'),
        'secret'  => env('STRIPE_SECRET'),
        'webhook' => [
            'secret'    => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'twilio' => [
        'enabled'        => env('TWILIO_ENABLED', false),
        'account_sid'    => env('TWILIO_SID', 'somerandomstring'),
        'auth_token'     => env('TWILIO_TOKEN', 'somerandomstring'),
        'from'           => env('TWILIO_FROM', 'somerandomstring'),
        'twiml_app_sid'  => env('TWIML_APP_SID', 'somerandomstring'),
        'cpm-caller-url' => env('CPM_CALLER_URL', ''),
    ],

    'cpm' => [
        'wellness_docs_url' => env('CPM_WELLNESS_DOCS_URL', null),
        'ccd_importer_url'  => env('CPM_CCD_IMPORTER_URL', null),
    ],

];
