<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Providers;

use CircleLinkHealth\SelfEnrollment\Console\Commands\SelfEnrollmentManualInviteCommand;
use CircleLinkHealth\SelfEnrollment\Console\Commands\SendSelfEnrollmentReminders;
use Illuminate\Support\ServiceProvider;

class SelfEnrollmentProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfigurations();
        $this->publishPublicAssets();

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
//        $this->app->register(RouteServiceProvider::class);

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
