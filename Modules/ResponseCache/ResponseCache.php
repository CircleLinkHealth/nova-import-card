<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache;

use CircleLinkHealth\ResponseCache\CacheProfiles\CacheProfile;
use CircleLinkHealth\ResponseCache\Hasher\RequestHasher;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseCache
{
    /** @var ResponseCache */
    protected $cache;

    /** @var CacheProfile */
    protected $cacheProfile;

    /** @var RequestHasher */
    protected $hasher;

    public function __construct(ResponseCacheRepository $cache, RequestHasher $hasher, CacheProfile $cacheProfile)
    {
        $this->cache        = $cache;
        $this->hasher       = $hasher;
        $this->cacheProfile = $cacheProfile;
    }

    public function cacheResponse(Request $request, Response $response, $lifetimeInMinutes = null): Response
    {
        if (config('responsecache.add_cache_time_header')) {
            $response = $this->addCachedHeader($response);
        }

        $this->cache->put(
            $this->hasher->getHashFor($request),
            $response,
            ($lifetimeInMinutes) ? intval($lifetimeInMinutes) : $this->cacheProfile->cacheRequestUntil($request)
        );

        return $response;
    }

    public function clear()
    {
        $this->cache->clear();
    }

    public function enabled(Request $request): bool
    {
        return $this->cacheProfile->enabled($request);
    }

    /**
     * @deprecated use the new clear method, this is just an alias
     */
    public function flush()
    {
        $this->clear();
    }

    /**
     * @param array|string $uris
     */
    public function forget($uris): self
    {
        $uris = is_array($uris) ? $uris : func_get_args();

        collect($uris)->each(function ($uri) {
            $request = Request::create(url($uri));
            $hash = $this->hasher->getHashFor($request);

            if ($this->cache->has($hash)) {
                $this->cache->forget($hash);
            }
        });

        return $this;
    }

    public function getCachedResponseFor(Request $request): Response
    {
        return $this->cache->get($this->hasher->getHashFor($request));
    }

    public function hasBeenCached(Request $request): bool
    {
        return config('responsecache.enabled')
            ? $this->cache->has($this->hasher->getHashFor($request))
            : false;
    }

    public function shouldCache(Request $request, Response $response): bool
    {
        if ($request->attributes->has('responsecache.doNotCache')) {
            return false;
        }

        if ( ! $this->cacheProfile->shouldCacheRequest($request)) {
            return false;
        }

        return $this->cacheProfile->shouldCacheResponse($response);
    }

    protected function addCachedHeader(Response $response): Response
    {
        $clonedResponse = clone $response;

        $clonedResponse->headers->set('laravel-responsecache', 'cached on '.date('Y-m-d H:i:s'));

        return $clonedResponse;
    }
}
