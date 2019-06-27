<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Spatie\ResponseCache\CacheProfiles;

use Illuminate\Http\Request;

class CacheAllSuccessfulGetRequests extends \Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests
{
    public function cacheNameSuffix(Request $request): string
    {
        return session()->get(\App\Constants::VIEWING_PATIENT, parent::cacheNameSuffix($request));
    }

    public function shouldCacheRequest(Request $request): bool
    {
        if ($this->isRunningInConsole()) {
            return false;
        }

        return $request->isMethod('get');
    }
}
