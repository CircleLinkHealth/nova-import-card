<?php

//@require '/var/vhosts/cpm/global-config.php';

/*
define('ENVIRONMENT', 'development');
define( 'SHORTINIT', TRUE );
define('WP_DEBUG', true);
define('WP_USE_THEMES', false);
define('ABSPATH', '/var/vhosts/cpm/wordpressmu/');
define('WPINC', 'wp-includes');
define('WP_MEMORY_LIMIT', '128M');
$_SERVER[ 'HTTP_HOST' ] = $global_config['domain_current_site'];
*/

/* END */
include __DIR__ . '/wp/wp-includes/class-phpass.php';
include __DIR__ . '/wp/wp-includes/pluggable.php';


/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/

require __DIR__.'/../bootstrap/autoload.php';

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

$kernel = $app->make('Illuminate\Contracts\Http\Kernel');

$response = $kernel->handle(
	$request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
