<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\InvoicesDownload;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class CardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            Nova::script('invoices-download', __DIR__.'/../dist/js/card.js');
            Nova::style('invoices-download', __DIR__.'/../dist/css/card.css');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Register the card's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('nova-vendor/invoices-download')
            ->group(__DIR__.'/../routes/api.php');
    }
}
