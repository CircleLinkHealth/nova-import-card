<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\CacheProfiles;

use Illuminate\Http\Request;

class CacheAllSuccessfulGetRequests extends CacheAllSuccessfulGetRequestsSpatie
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
}
