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
        'url'               => env('CPM_URL', null),
        'wellness_docs_url' => env('CPM_WELLNESS_DOCS_URL', null),
        'ccd_importer_url'  => env('CPM_CCD_IMPORTER_URL', null),
    ],
];
