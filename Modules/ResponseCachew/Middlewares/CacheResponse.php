<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\Middlewares;

use CircleLinkHealth\ResponseCache\Events\CacheMissed;
use CircleLinkHealth\ResponseCache\Events\ResponseCacheHit;
use CircleLinkHealth\ResponseCache\ResponseCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /** @var \CircleLinkHealth\ResponseCache\ResponseCache */
    protected $responseCache;

    public function __construct(ResponseCache $responseCache)
    {
        $this->responseCache = $responseCache;
    }

    public function handle(Request $request, Closure $next, $lifetimeInMinutes = null): Response
    {
        if ($this->responseCache->enabled($request)) {
            if ($this->responseCache->hasBeenCached($request)) {
                event(new ResponseCacheHit($request));

                return $this->responseCache->getCachedResponseFor($request);
            }
        }

        $response = $next($request);

        if ($this->responseCache->enabled($request)) {
            if ($this->responseCache->shouldCache($request, $response)) {
                $this->responseCache->cacheResponse($request, $response, $lifetimeInMinutes);
            }
        }

        event(new CacheMissed($request));

        return $response;
    }
}
