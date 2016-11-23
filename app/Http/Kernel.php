<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\LogoutIfAccessDisabled::class,
            \App\Http\Middleware\ClearPatientSessions::class,
        ],
        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Fideloper\Proxy\TrustProxies::class,
        \App\Http\Middleware\Secure::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        //Laravel Middleware
        'auth'       => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'   => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can'        => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'      => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle'   => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        //CLH Middleware
        'ability'                    => \Zizaco\Entrust\Middleware\EntrustAbility::class,
        'aprima.ccdapi.auth.adapter' => Middleware\AprimaCcdApiAuthAdapter::class,
        'authApiCall'                => \App\Http\Middleware\AuthenticateApiCall::class,
        'getRedoxAccessToken'        => \App\Http\Middleware\GetRedoxAccessToken::class,
        'permission'                 => \Zizaco\Entrust\Middleware\EntrustPermission::class,
        'patientProgramSecurity'     => \App\Http\Middleware\PatientProgramSecurity::class,
        'patient.session'            => \App\Http\Middleware\CheckPatientSession::class,
        'role'                       => \Zizaco\Entrust\Middleware\EntrustRole::class,
    ];

}
