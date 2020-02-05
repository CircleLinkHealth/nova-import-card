<?php

namespace App\Providers;

use App\Console\Commands\MakeMigrationInModulesFolder;
use Illuminate\Support\Facades\Auth;
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
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Auth::provider('awv', function ($app, array $config) {
            return new AwvUserProvider($app['hash'], $config['model']);
        });

//        $this->app->extend('make:migration', function () {
//            return new MakeMigrationInModulesFolder;
//        });
    }
}
