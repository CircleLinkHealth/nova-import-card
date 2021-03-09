<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\Vapor;

use Illuminate\Support\Facades\Route;

trait DefinesRoutes
{
    /**
     * Ensure that Vapor's internal routes are defined.
     *
     * @return void
     */
    public function ensureRoutesAreDefined()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::post(
            '/vapor/signed-storage-url',
            Contracts\SignedStorageUrlController::class.'@store'
        )->middleware(config('vapor.middleware', 'web'));
    }
}
