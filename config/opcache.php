<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

$opcacheUrl = env('OPCACHE_URL', env('APP_URL', null));

$opcacheUrl = str_replace('${HEROKU_APP_NAME}', getenv('HEROKU_APP_NAME'), $opcacheUrl);

return [
    'url'         => $opcacheUrl,
    'prefix'      => 'opcache-api',
    'verify'      => true,
    'headers'     => [],
    'directories' => [
        base_path('app'),
        base_path('bootstrap'),
        base_path('public'),
        base_path('resources/lang'),
        base_path('routes'),
        base_path('storage/framework/views'),
        base_path('vendor/appstract'),
        base_path('vendor/composer'),
        base_path('vendor/laravel/framework'),
        base_path('vendor/circlelinkhealth/'),
    ],
    'exclude' => [
        'test',
        'Test',
        'tests',
        'Tests',
        'stub',
        'Stub',
        'stubs',
        'Stubs',
        'dumper',
        'Dumper',
    ],
];
