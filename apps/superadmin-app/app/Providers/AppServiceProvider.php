<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapThree();
    
        Queue::before(function (JobProcessing $event) {
            Log::debug("Starting Job {$event->job->resolveName()}");
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (isProductionEnv()) {
            $this->app->instance('path.storage', sys_get_temp_dir());
        }
    }
}
