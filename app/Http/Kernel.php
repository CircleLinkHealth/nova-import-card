<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http;

use App\Http\Middleware\ACL\ProviderDashboardACL;
use App\Http\Middleware\AdminOrPracticeStaff;
use App\Http\Middleware\CareAmbassadorAPI;
use App\Http\Middleware\CheckCarePlanMode;
use App\Http\Middleware\CheckOnboardingInvite;
use App\Http\Middleware\CheckPatientUserData;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnrollmentCenter;
use App\Http\Middleware\LogoutIfAccessDisabled;
use App\Http\Middleware\PatientProgramSecurity;
use App\Http\Middleware\SentryContext;
use App\Http\Middleware\VerifyCsrfToken;
use CircleLinkHealth\TwoFA\Http\Middleware\AuthyMiddleware;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Http\Middleware\FrameGuard;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;
use Michalisantoniou6\Cerberus\Middleware\CerberusAbility;
use Michalisantoniou6\Cerberus\Middleware\CerberusPermission;
use Michalisantoniou6\Cerberus\Middleware\CerberusRole;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        FrameGuard::class,
        SentryContext::class,
    ];

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
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            LogoutIfAccessDisabled::class,
            CreateFreshApiToken::class,
            AuthyMiddleware::class,
        ],
        'sessions' => [
            StartSession::class,
        ],
        'api' => [
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        //Laravel Middleware
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'         => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can'              => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'           => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        //CLH Middleware
        'ability'                => CerberusAbility::class,
        'permission'             => CerberusPermission::class,
        'patientProgramSecurity' => PatientProgramSecurity::class,
        'providerDashboardACL'   => ProviderDashboardACL::class,
        'role'                   => CerberusRole::class,
        'verify.invite'          => CheckOnboardingInvite::class,
        'check.careplan.mode'    => CheckCarePlanMode::class,
        'checkPatientUserData'   => CheckPatientUserData::class,
        'enrollmentCenter'       => EnrollmentCenter::class,
        'careAmbassadorAPI'      => CareAmbassadorAPI::class,
        'adminOrPracticeStaff'   => AdminOrPracticeStaff::class,
    ];
}
