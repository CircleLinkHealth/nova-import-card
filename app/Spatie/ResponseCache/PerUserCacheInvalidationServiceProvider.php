<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Spatie\ResponseCache;

use App\Spatie\ResponseCache\InvalidationProfiles\FlushUserCacheOnAnyRelatedModelChange;
use Illuminate\Support\ServiceProvider;

class PerUserCacheInvalidationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
    }

    /**
     * Register services.
     */
    public function register()
    {
        if (config('responsecache.enabled')) {
            $this->app->singleton('invalidate-cache', FlushUserCacheOnAnyRelatedModelChange::class);

            $this->app->make('invalidate-cache')->registerEloquentEventListener();
        }
    }
}
