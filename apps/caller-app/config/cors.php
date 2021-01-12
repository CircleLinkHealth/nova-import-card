<?php

$isProd = (env('APP_ENV', 'production') === 'production' || env('APP_ENV', 'production') === 'worker');

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

    'supportsCredentials' => true,
    'allowedOrigins'      => $isProd ? ['*careplanmanager.com'] : ['*'],
    'allowedHeaders'      => ['*'],
    'allowedMethods'      => ['*'],
    'exposedHeaders'      => [],
    'maxAge'              => 0,

];
