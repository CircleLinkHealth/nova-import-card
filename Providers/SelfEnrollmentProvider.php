<?php

namespace CircleLinkHealth\SelfEnrollment\Providers;

use CircleLinkHealth\SamlSp\Providers\RouteServiceProvider;
use CircleLinkHealth\SelfEnrollment\Console\Commands\SelfEnrollmentManualInviteCommand;
use CircleLinkHealth\SelfEnrollment\Console\Commands\SendSelfEnrollmentReminders;
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
    }
}
