<?php

namespace CircleLinkHealth\Core;

use Carbon\Carbon;
use Closure;
use Illuminate\Cache\CacheManager as CacheManager;

/**
 * @mixin \Illuminate\Contracts\Cache\Repository
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

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @param string $key
     * @param \DateTimeInterface|\DateInterval|float|int $minutes
     * @param \Closure $callback
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

    public function clear()
    {
        //make sure array store is cleared
        $this->store('array')->clear();

        return parent::clear();
    }

    /**
     * Get threshold number of minutes where we always
     * cache in array store.
     *
     * Default: 2 minutes
     *
     * @return int
     */
    private function getArrayStoreThreshold(): int
    {
        return config('core.smart_cache_array_store_threshold_minutes') ?? 2;
    }

    private function getTimeToRememberInMinutes($val)
    {
        if (is_int($val) || is_float($val)) {
            return $val;
        } else if ($val instanceof Carbon) {
            /** @var Carbon $val */
            $carbonVal = $val;
            $now       = now();

            return $carbonVal->diffInMinutes($now);
        } else if ($val instanceof \DateInterval) {
            /** @var \DateInterval $diVal */
            $diVal = $val;

            return $diVal->i;
        }

        return $val;
    }

}