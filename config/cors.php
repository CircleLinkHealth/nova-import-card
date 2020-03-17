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

    'supportsCredentials'    => true,
    'allowedOrigins'         => (env('APP_ENV', 'production') === 'production' || env('APP_ENV',
            'production') === 'worker' || env('APP_ENV', 'production') === 'staging')
        ? ['*careplanmanager.com', '*clh-staging.com']
        : ['*.ngrok.io', '*.test'],
    'allowedOriginsPatterns' => [],
    'allowedHeaders'         => ['*'],
    'allowedMethods'         => ['*'],
    'exposedHeaders'         => [],
    'maxAge'                 => 0,

];
