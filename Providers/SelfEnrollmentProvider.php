<?php

namespace CircleLinkHealth\SelfEnrollment\Providers;

use CircleLinkHealth\SamlSp\Providers\RouteServiceProvider;
use CircleLinkHealth\SelfEnrollment\Console\Commands\SelfEnrollmentManualInviteCommand;
use CircleLinkHealth\SelfEnrollment\Console\Commands\SendSelfEnrollmentReminders;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class SelfEnrollmentProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->commands([
            SelfEnrollmentManualInviteCommand::class,
            SendSelfEnrollmentReminders::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

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
}
