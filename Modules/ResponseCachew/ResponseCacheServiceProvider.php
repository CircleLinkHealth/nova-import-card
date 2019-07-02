<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache;

use CircleLinkHealth\ResponseCache\CacheProfiles\CacheProfile;
use CircleLinkHealth\ResponseCache\Commands\Clear;
use CircleLinkHealth\ResponseCache\Commands\Flush;
use CircleLinkHealth\ResponseCache\InvalidationProfiles\FlushUserCacheOnAnyRelatedModelChange;
use Illuminate\Cache\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class ResponseCacheServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/responsecache.php' => config_path('responsecache.php'),
        ], 'config');

        $this->app->bind(CacheProfile::class, function (Container $app) {
            return $app->make(config('responsecache.cache_profile'));
        });

        $this->app->when(ResponseCacheRepository::class)
            ->needs(Repository::class)
            ->give(function (): Repository {
                $repository = $this->app['cache']->store(config('responsecache.cache_store'));

                $userId = optional(request()->route())->parameter('patientId') ?? auth()->id();

                if ( ! empty(config('responsecache.cache_tag')) && ! empty($userId)) {
                    return $repository->tags(config('responsecache.cache_tag')."user_$userId");
                }

                return $repository;
            });

        $this->app->singleton('responsecache', ResponseCache::class);

        $this->app->singleton('invalidate-cache', FlushUserCacheOnAnyRelatedModelChange::class);

        if (config('responsecache.enabled') && ! request()->isMethodCacheable()) {
            $this->app->make('invalidate-cache')->registerEloquentEventListener();
        }

        $this->app['command.responsecache:flush'] = $this->app->make(Flush::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                Flush::class,
                Clear::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/responsecache.php', 'responsecache');
    }
}
