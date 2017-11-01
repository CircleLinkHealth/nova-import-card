<?php

return [

    /**
     * Should we send errors to Airbrake
     */
    'enabled' => true,

    /**
     * Airbrake API key
     */
    'api_key' => '3e8e4380-723b-3c1b-4810-f327dde97cfe',

    /**
     * Should we send errors async
     */
    'async' => false,

    /**
     * Which enviroments should be ingored? (ex. local)
     */
    'ignore_environments' => [
        'local',
        'test',
        'stage',
        'testing',
    ],

    /**
     * Ignore these exceptions
     */
    'ignore_exceptions' => [],

    /**
     * Connection to the airbrake server
     */
    'connection' => [

        'host' => 'exceptions.codebasehq.com',

        'port' => '443',

        'resource' => '/notifier_api/v2/notices',

        'secure' => true,

        'verifySSL' => true
    ]

];
