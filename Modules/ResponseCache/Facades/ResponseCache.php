<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\ResponseCache\Facades;

use Illuminate\Support\Facades\Facade;

class ResponseCache extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @see \CircleLinkHealth\ResponseCache\ResponseCache
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'responsecache';
    }
}
