<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\View\Composers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class FabComposer extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function boot()
    {
        View::composer(['partials.fab'], function ($view) {

            $sessionUser = auth()->user();

            if (! $sessionUser){
                throw new \Exception('No authenticated User found.', 500);
            }

            $canAddOfflineActivity = $sessionUser->hasPermission('offlineActivity.create');

            $view->with(compact([
                'canAddOfflineActivity'
            ]));
        });

    }

    /**
     * Register the service provider.
     */
    public function register()
    {
    }
}
