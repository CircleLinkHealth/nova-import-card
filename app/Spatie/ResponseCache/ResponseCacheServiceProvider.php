<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Spatie\ResponseCache;

use Illuminate\Cache\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Spatie\ResponseCache\CacheProfiles\CacheProfile;
use Spatie\ResponseCache\Commands\Clear;
use Spatie\ResponseCache\Commands\Flush;
use Spatie\ResponseCache\ResponseCache;
use Spatie\ResponseCache\ResponseCacheRepository;

class ResponseCacheServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->bind(CacheProfile::class, function (Container $app) {
            return $app->make(config('responsecache.cache_profile'));
        });

        $this->app->when(ResponseCacheRepository::class)
            ->needs(Repository::class)
            ->give(function (): Repository {
                $repository = $this->app['cache']->store(config('responsecache.cache_store'));
                if ( ! empty(config('responsecache.cache_tag'))) {
                    $userId = optional(request()->route())->parameter('patientId') ?? auth()->id();

                    return $repository->tags(config('responsecache.cache_tag')."user_$userId");
                }

                return $repository;
            });

        $this->app->singleton('responsecache', ResponseCache::class);

        $this->app['command.responsecache:flush'] = $this->app->make(Flush::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                Flush::class,
                Clear::class,
            ]);
        }
    }
}
