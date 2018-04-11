<?php namespace App\Providers;

use App\CLH\Helpers\StringManipulation;
use Illuminate\Support\ServiceProvider;

class StringManipulationServiceProvider extends ServiceProvider
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
        $this->app->bind('stringManipulation', function () {
            return new StringManipulation();
        });
    }
}
