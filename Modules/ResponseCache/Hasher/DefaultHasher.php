<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\Hasher;

use CircleLinkHealth\ResponseCache\CacheProfiles\CacheProfile;
use Illuminate\Http\Request;

class DefaultHasher implements RequestHasher
{
    /** @var \CircleLinkHealth\ResponseCache\CacheProfiles\CacheProfile */
    protected $cacheProfile;

    public function __construct(CacheProfile $cacheProfile)
    {
        $this->cacheProfile = $cacheProfile;
    }

    public function getHashFor(Request $request): string
    {
        return "responsecache-{$request->getRequestUri()}/{$request->getMethod()}/{$this->cacheProfile->cacheNameSuffix($request)}";
    }
}
