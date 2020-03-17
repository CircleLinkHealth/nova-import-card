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
    'allowedOrigins'         => ['*careplanmanager.com', '*clh-staging.com', '*.ngrok.io', '*.test'],
    'allowedOriginsPatterns' => ['*careplanmanager.com', '*clh-staging.com', '*.ngrok.io', '*.test'],
    'allowedHeaders'         => ['*'],
    'allowedMethods'         => ['*'],
    'exposedHeaders'         => [],
    'maxAge'                 => 0,

];
