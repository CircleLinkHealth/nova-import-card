<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\CacheProfiles;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CacheAllSuccessfulGetRequests extends BaseCacheProfile
{
    public function cacheNameSuffix(Request $request): string
    {
        return session()->get(\App\Constants::VIEWING_PATIENT, auth()->id() ?? '');
    }

    public function shouldCacheRequest(Request $request): bool
    {
        if ($this->isRunningInConsole()) {
            return false;
        }

        return $request->isMethod('get');
    }
    
    public function shouldCacheResponse(Response $response): bool
    {
        if (! $this->hasCacheableResponseCode($response)) {
            return false;
        }
        
        if (! $this->hasCacheableContentType($response)) {
            return false;
        }
        
        return true;
    }
    
    public function hasCacheableResponseCode(Response $response): bool
    {
        return $response->isSuccessful() || $response->isRedirection();
    }
    
    public function hasCacheableContentType(Response $response)
    {
        $contentType = $response->headers->get('Content-Type', '');
        
        return Str::startsWith($contentType, 'text');
    }
}
