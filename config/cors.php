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
    'allowedOrigins'         => ['*'],
    'allowedHeaders'         => ['*'],
    'allowedMethods'         => ['*'],
    'exposedHeaders'         => [],
    'maxAge'                 => 0,

];
