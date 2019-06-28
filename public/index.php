<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
 |--------------------------------------------------------------------------
 | Blackfire SDK integration
 |--------------------------------------------------------------------------
 |
 | Enable if you want to run blackfire using
 | an API testing tool (eg. Postman / JMeter).
 | Then, make sure you set Black_Fire_Trigger in headers.
 |
 */
//if (isset($_SERVER['HTTP_BLACK_FIRE_TRIGGER'])) {
//    // let's create a client
//    $blackfire = new \Blackfire\Client();
//    // then start the probe
//    $probe = $blackfire->createProbe();
//
//    // When runtime shuts down, let's finish the profiling session
//    register_shutdown_function(function () use ($blackfire, $probe) {
//        // See the PHP SDK documentation for using the $profile object
//        $profile = $blackfire->endProbe($probe);
//    });
//}

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->alias('request', 'App\SafeRequest');

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = App\SafeRequest::capture()
);

if (config('responsecache.enabled')) {
    app('invalidate-cache')->flushCandidates();
}

$response->send();

$kernel->terminate($request, $response);
