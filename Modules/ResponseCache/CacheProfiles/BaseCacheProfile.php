<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\CacheProfiles;

use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

abstract class BaseCacheProfile implements CacheProfile
{
    // Set a string to add to differentiate this request from others.
    public function cacheNameSuffix(Request $request): string
    {
        if (\Auth::check()) {
            return \Auth::user()->id;
        }

        return '';
    }

    // Return the time when the cache must be invalided.
    public function cacheRequestUntil(Request $request): DateTime
    {
        return Carbon::now()->addMinutes(
            config('responsecache.cache_lifetime_in_minutes')
        );
    }

    public function enabled(Request $request): bool
    {
        return config('responsecache.enabled');
    }

    public function isRunningInConsole(): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        return app()->runningInConsole();
    }
}
