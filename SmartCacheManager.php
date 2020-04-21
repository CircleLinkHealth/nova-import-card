<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core;

use Carbon\Carbon;
use Closure;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository;

/**
 * @mixin Repository
 */
class SmartCacheManager extends CacheManager
{
    /**
     * Create a new Cache manager instance.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function clear()
    {
        //make sure array store is cleared
        $this->store('array')->clear();

        return parent::clear();
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param string                                     $key
     * @param \DateInterval|\DateTimeInterface|float|int $minutes
     *
     * @return mixed
     */
    public function remember($key, $minutes, Closure $callback)
    {
        $thresholdMinutes = $this->getArrayStoreThreshold();
        $val              = $this->getTimeToRememberInMinutes($minutes);
        if ($val <= $thresholdMinutes) {
            return \Cache::store('array')->remember($key, $minutes, $callback);
        }

        return parent::remember($key, $minutes, $callback);
    }

    /**
     * Get threshold number of minutes where we always
     * cache in array store.
     *
     * Default: 2 minutes
     */
    private function getArrayStoreThreshold(): int
    {
        return config('core.smart_cache_array_store_threshold_minutes') ?? 2;
    }

    private function getTimeToRememberInMinutes($val)
    {
        if (is_int($val) || is_float($val)) {
            return $val;
        }
        if ($val instanceof Carbon) {
            /** @var Carbon $val */
            $carbonVal = $val;
            $now       = now();

            return $carbonVal->diffInMinutes($now);
        }
        if ($val instanceof \DateInterval) {
            /** @var \DateInterval $diVal */
            $diVal = $val;

            return $diVal->i;
        }

        return $val;
    }
}
