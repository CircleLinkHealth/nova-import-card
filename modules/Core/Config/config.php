<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use CircleLinkHealth\Customer\CpmConstants;

return [
    'name' => 'Core',

    'is_production_env'   => env('IS_PRODUCTION_SERVER', false),
    'is_queue_worker_env' => env('IS_QUEUE_WORKER_SERVER', false),

    /*
     * Exceptions of type Illuminate\Database\QueryException are likely to contain PHI.
     * To prevent leaking PHI to any bug tracker we use, by default we disable forwarding these exceptions to the bug tracker.
     */
    'forward_query_exceptions_to_bug_tracker' => env('FORWARD_QUERY_EXCEPTIONS_TO_BUG_TRACKER', false),

    'store_query_exceptions_in_db' => env('STORE_QUERY_EXCEPTIONS_IN_DB', true),

    'smart_cache_array_store_threshold_minutes' => env('SMART_CACHE_ARRAY_STORE_THRESHOLD_MINUTES', 2),

    'apps' => [
        'cpm-admin' => [
            'url' => env('CPM_ADMIN_APP_URL', ''),
        ],
        'cpm-provider' => [
            'url' => env('CPM_PROVIDER_APP_URL', ''),
        ],
        'self-enrollment' => [
            'url' => env('CPM_SELF_ENROLLMENT_APP_URL', ''),
        ]
    ],

    'services' => [
        'phaxio' => [
            'host' => 'https://api.phaxio.com/v2.1/',

            'key'    => env('PHAXIO_KEY', null),
            'secret' => env('PHAXIO_SECRET', null),
        ],
        'emr-direct' => [
            'user'                 => env('EMR_DIRECT_USER'),
            'test_user'            => env('EMR_DIRECT_TEST_USER'),
            'password'             => env('EMR_DIRECT_PASSWORD'),
            'conc-keys-pem-path'   => env('EMR_DIRECT_CONC_KEYS_PEM_PATH'),
            'pass-phrase'          => env('EMR_DIRECT_PASS_PHRASE'),
            'server-cert-pem-path' => env('EMR_DIRECT_SERVER_CERT_PEM_PATH'),
            'mail-server'          => env('EMR_DIRECT_MAIL_SERVER'),
            'port'                 => env('EMR_DIRECT_PORT'),
            'client-cert-filename' => env('EMR_CLIENT_CERT_FILENAME'),
            'server-cert-filename' => env('EMR_SERVER_CERT_FILENAME'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CPM queues
    |--------------------------------------------------------------------------
    |
    | Jobs can live in a module, and therefore dispatched by different apps.
    | Consider StoreTimeTracking. It may be dispatched from admin or provider app
    | to queue "high". For this reason we need a unique name for "high" and "low"
    | queues in each app.
    |
    */
    'cpm_queues' => [
        CpmConstants::LOW_QUEUE => [
            'name' => env('LOW_CPM_QUEUE_NAME', null),
        ],
        CpmConstants::HIGH_QUEUE => [
            'name' => env('HIGH_CPM_QUEUE_NAME', null),
        ],
    ],
];
