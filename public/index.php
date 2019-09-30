<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Constants;

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

$app = require_once __DIR__.'/../bootstrap/app.php';
//$app->register(Illuminate\Session\SessionServiceProvider::class);
//define('LOAD_APP', microtime(true) - LARAVEL_START);

//$n = (new Dotenv\Dotenv(__DIR__.'/../', '.redis-creds'))->load();
//
//$redis = new Predis\Client([
//    'host'     => $n[0],
//    'password' => 'NULL' === $n[1] ? null : $n[1],
//    'port'     => $n[2],
//]);
//
//if ($keys = $redis->keys("*{$_SERVER['REQUEST_URI']}*")) {
//    if (is_array($keys) && 1 == count($keys)) {
//        $r = $redis->get($keys[0]);
//
//        if ($r) {
//            try {
//                $response = (new CircleLinkHealth\ResponseCache\ResponseSerializer())->unserialize($r);
////                @todo: load session module and replace csrf token
////                (new \CircleLinkHealth\ResponseCache\Replacers\CsrfTokenReplacer())->replaceInCachedResponse($response);
//
//                return $response->send();
//            } catch (CircleLinkHealth\ResponseCache\Exceptions\CouldNotUnserialize $e) {
//            }
//        }
//    }
//}

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
//define('LARAVEL_REQUIRE_APP', microtime(true) - LOAD_APP - LARAVEL_START);

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
//define('LARAVEL_BEFORE_KERNEL', microtime(true) - LARAVEL_REQUIRE_APP - LOAD_APP - LARAVEL_START);

$response = $kernel->handle(
    $request = App\SafeRequest::capture()
);

//define('LARAVEL_AFTER_KERNEL', microtime(true) - LARAVEL_BEFORE_KERNEL - LARAVEL_REQUIRE_APP - LOAD_APP - LARAVEL_START);

if (config('responsecache.enabled') && ! $request->isMethodCacheable()) {
    app('invalidate-cache')->flushCandidates();
}

//define('LARAVEL_AFTER_RESPONCE_CACHE', microtime(true) - LARAVEL_BEFORE_KERNEL - LARAVEL_REQUIRE_APP - LOAD_APP - LARAVEL_START - LARAVEL_AFTER_KERNEL);

//dd([
//       'LARAVEL_START' => LARAVEL_START,
//       'LOAD_APP' => LOAD_APP,
//       'LARAVEL_REQUIRE_APP' => LARAVEL_REQUIRE_APP,
//       'LARAVEL_BEFORE_KERNEL' => LARAVEL_BEFORE_KERNEL,
//       'LARAVEL_AFTER_KERNEL' => LARAVEL_AFTER_KERNEL,
//       'LARAVEL_AFTER_RESPONCE_CACHE' => LARAVEL_AFTER_RESPONCE_CACHE,
//   ]);

$response->send();

//Flush viewing patient after we send response
$flushed = session()->pull(Constants::VIEWING_PATIENT);

$kernel->terminate($request, $response);
