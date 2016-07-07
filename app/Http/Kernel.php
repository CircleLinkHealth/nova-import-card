<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',
		'Fideloper\Proxy\TrustProxies',
		'App\Http\Middleware\Secure',
//		'App\Http\Middleware\VerifyCsrfToken',
//		This ^^ is commented out to allow forms to be submitted from other sites
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'aprima.ccdapi.auth.adapter' => Middleware\AprimaCcdApiAuthAdapter::class,
		'auth' => \App\Http\Middleware\Authenticate::class,
		'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
		'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
		'authApiCall' => \App\Http\Middleware\AuthenticateApiCall::class,
        'getRedoxAccessToken' => \App\Http\Middleware\GetRedoxAccessToken::class,
        'patientProgramSecurity' => \App\Http\Middleware\PatientProgramSecurity::class,
		'role' => \Zizaco\Entrust\Middleware\EntrustRole::class,
		'permission' => \Zizaco\Entrust\Middleware\EntrustPermission::class,
		'ability' => \Zizaco\Entrust\Middleware\EntrustAbility::class,
//		'impersonation.check' => Middleware\ImpersonationMiddleware::class,
	];

}
