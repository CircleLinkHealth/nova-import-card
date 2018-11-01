<?php namespace App\Http;

use App\Http\Middleware\ACL\ProviderDashboardACL;
use App\Http\Middleware\AprimaCcdApiAuthAdapter;
use App\Http\Middleware\CheckCarePlanMode;
use App\Http\Middleware\CheckOnboardingInvite;
use App\Http\Middleware\CheckWebSocketServer;
use App\Http\Middleware\DisableDebugbar;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\LogoutIfAccessDisabled;
use App\Http\Middleware\PatientProgramSecurity;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Http\Middleware\FrameGuard;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;
use Michalisantoniou6\Cerberus\Middleware\CerberusAbility;
use Michalisantoniou6\Cerberus\Middleware\CerberusPermission;
use Michalisantoniou6\Cerberus\Middleware\CerberusRole;

class Kernel extends HttpKernel
{

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            LogoutIfAccessDisabled::class,
            CreateFreshApiToken::class,
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
        CheckForMaintenanceMode::class,
        TrustProxies::class,
        FrameGuard::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        //Laravel Middleware
        'auth'                       => Authenticate::class,
        'auth.basic'                 => AuthenticateWithBasicAuth::class,
        'bindings'                   => SubstituteBindings::class,
        'can'                        => Authorize::class,
        'guest'                      => RedirectIfAuthenticated::class,
        'throttle'                   => ThrottleRequests::class,

        //CLH Middleware
        'ability'                    => CerberusAbility::class,
        'aprima.ccdapi.auth.adapter' => AprimaCcdApiAuthAdapter::class,
        'disable-debugbar'           => DisableDebugbar::class,
        'permission'                 => CerberusPermission::class,
        'patientProgramSecurity'     => PatientProgramSecurity::class,
        'checkWebSocketServer'       => CheckWebSocketServer::class,
        'providerDashboardACL'       => ProviderDashboardACL::class,
        'role'                       => CerberusRole::class,
        'verify.invite'              => CheckOnboardingInvite::class,
        'check.careplan.mode'        => CheckCarePlanMode::class,
    ];
}
