<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */

    'supportsCredentials' => false,
    'allowedOrigins'      => ('production' === env('APP_ENV', 'production') || 'worker' === env(
        'APP_ENV',
        'production'
    ) || 'staging' === env('APP_ENV', 'production'))
        ? ['*careplanmanager.com', '*awv-staging.herokuapp.com']
        : ['*.ngrok.io', '*.test', '*awv-staging.herokuapp.com'],
    'allowedOriginsPatterns' => [],
    'allowedHeaders'         => ['*'],
    'allowedMethods'         => ['*'],
    'exposedHeaders'         => [],
    'maxAge'                 => 0,
];
