<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

return [
    'url'         => env('OPCACHE_URL', config('app.url')),
    'verify_ssl'  => false,
    'verify_host' => 0,
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
