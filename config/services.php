<?php

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
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'twilio' => [
        'enabled'          => env('TWILIO_ENABLED', false),
        'sid'              => env('TWILIO_SID', 'somerandomstring'),
        'token'            => env('TWILIO_TOKEN', 'somerandomstring'),
        'from'             => env('TWILIO_FROM', 'somerandomstring'),
        'twiml-app-sid'    => env('TWIML_APP_SID', 'somerandomstring'),
        'allow-conference' => env('TWIML_ALLOW_CONFERENCE', false),
        'allow-recording'  => env('TWIML_ALLOW_RECORDING', false),
    ],

];
