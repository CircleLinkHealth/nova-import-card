<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Providers;

use App\Contracts\HasUniqueIdentifierForDebounce;
use Closure;
use Mpbarlow\LaravelQueueDebouncer\Contracts\CacheKeyProvider;
use ReflectionFunction;

class DebouncerCacheKeyProvider implements CacheKeyProvider
{
    /**
     * {@inheritdoc}
     */
    public function getKey($job): string
    {
        $identifier = $job instanceof Closure
            ? sha1((string) (new ReflectionFunction($job)))
            : get_class($job);

        if ($job instanceof HasUniqueIdentifierForDebounce) {
            $identifier .= $job->getUniqueIdentifier();
        }

        return config('queue_debouncer.cache_prefix').':'.$identifier;
    }
}
