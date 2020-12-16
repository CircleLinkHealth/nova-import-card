<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

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
    ],

    'services' => [
        'phaxio' => [
            'host' => 'https://api.phaxio.com/v2.1/',

            'key'    => env('PHAXIO_KEY', null),
            'secret' => env('PHAXIO_SECRET', null),
        ],
    ],
];
