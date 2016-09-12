<?php

namespace App\Providers;

use Dusterio\AwsWorker\Integrations\LaravelServiceProvider;
use Illuminate\Support\ServiceProvider;

class AWSWorkerServiceProvider extends ServiceProvider
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
        if (in_array($this->app->environment(), [
            'worker',
            'worker-staging'
        ])) {
            $this->app->register(LaravelServiceProvider::class);
        }
    }
}
