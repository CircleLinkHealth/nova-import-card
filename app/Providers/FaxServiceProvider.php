<?php

namespace App\Providers;

use App\Contracts\Efax;
use App\Services\Phaxio\PhaxioService;
use Illuminate\Support\ServiceProvider;
use Phaxio\Phaxio;

class FaxServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Efax::class, function () {
            $config = config('phaxio');

            $mode = (app()->environment('production') || app()->environment('worker') || app()->environment('staging'))
                ? 'production'
                : 'test';

            $phaxio = new Phaxio($config[$mode]['key'], $config[$mode]['secret'], $config['host']);

            return new PhaxioService($phaxio);
        });
    }
}
