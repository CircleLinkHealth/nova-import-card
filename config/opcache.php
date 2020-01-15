<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

$opcacheUrl = env('OPCACHE_URL', config('app.url'));

$opcacheUrl = str_replace('${HEROKU_APP_NAME}', getenv('HEROKU_APP_NAME'), $opcacheUrl);

return [
    'url'         => $opcacheUrl,
    'verify_ssl'  => true,
    'verify_host' => 2,
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
    ],
];
