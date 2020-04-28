<?php

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
    'allowedOrigins'         => (env('APP_ENV', 'production') === 'production' || env('APP_ENV',
            'production') === 'worker' || env('APP_ENV', 'production') === 'staging')
        ? ['*careplanmanager.com', '*awv-staging.herokuapp.com']
        : ['*.ngrok.io', '*.test', '*awv-staging.herokuapp.com'],
    'allowedOriginsPatterns' => [],
    'allowedHeaders' => ['*'],
    'allowedMethods' => ['*'],
    'exposedHeaders' => [],
    'maxAge' => 0,

];
