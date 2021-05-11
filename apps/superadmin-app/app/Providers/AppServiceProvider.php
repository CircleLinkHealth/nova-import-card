<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
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
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Log::debug("isProductionEnv".isProductionEnv());
        Log::debug("sys_get_temp_dir".sys_get_temp_dir());
//        Nothing changed. Maybe below doesn't work?
//        if (isProductionEnv()) {
            $this->app->instance('path.storage', sys_get_temp_dir());
//        }
    }
}
