<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Providers;

use App\Providers\RouteServiceProvider;
use CircleLinkHealth\SelfEnrollment\Console\Commands\SelfEnrollmentManualInviteCommand;
use CircleLinkHealth\SelfEnrollment\Console\Commands\SendSelfEnrollmentReminders;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Console\InstallCommand;
use Laravel\VaporCli\Commands\NetworkCommand;

class SelfEnrollmentProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->publishConfigurations();
        $this->publishPublicAssets();

//        $viewPath = resource_path('views/modules/selfEnrollment');
//
//        $sourcePath = __DIR__.'/../Resources/views';
//
//        $this->publishes([
//            $sourcePath => $viewPath,
//        ], 'views');
//
//
//        $this->loadViewsFrom(array_merge(array_map(function ($path) {
//            return $path.'/modules/selfEnrollment';
//        }, Config::get('view.paths')), [$sourcePath]), 'selfEnrollment');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'selfEnrollment');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/selfEnrollment'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(SelfEnrollmentRouteProvider::class);
        $this->commands([
            SelfEnrollmentManualInviteCommand::class,
            SendSelfEnrollmentReminders::class,
        ]);
//        $this->app->singleton(SelfEnrollmentLetter::class);
    }

    private function publishConfigurations()
    {
//        $this->publishes([
//            __DIR__.'/../Config/services.php' => config_path('selfEnrollment.php'),
//        ],
//            'config'
//        );

        $this->mergeConfigFrom(
            __DIR__.'/../Config/services.php',
            'selfEnrollment'
        );
    }

    private function publishPublicAssets()
    {
//        $this->publishes([
//            __DIR__.'/../Public' => public_path('vendor/selfEnrollment'),
//        ], 'public');
    }
}
