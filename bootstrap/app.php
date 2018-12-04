<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    'Illuminate\Contracts\Http\Kernel',
    'App\Http\Kernel'
);

$app->singleton(
    'Illuminate\Contracts\Console\Kernel',
    'App\Console\Kernel'
);

$app->singleton(
    'Illuminate\Contracts\Debug\ExceptionHandler',
    'App\Exceptions\Handler'
);

///**
// * Configure Monolog.
// */
$app->configureMonologUsing(function (Monolog\Logger $monolog) {
    $filename = storage_path('logs/laravel-'.php_sapi_name().'.log');
    $handler = new Monolog\Handler\RotatingFileHandler($filename, config('app.log_max_files'));
    $monolog->pushHandler($handler);
});

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
