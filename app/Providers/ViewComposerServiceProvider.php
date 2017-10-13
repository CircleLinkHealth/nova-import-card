<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            if ($view->patient && $view->patient->hasProblem('Diabetes') && !$view->patient->hasProblem('Diabetes Type 1') && !$view->patient->hasProblem('Diabetes Type 2')) {

                $view->with('showBanner', true);
            }
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}