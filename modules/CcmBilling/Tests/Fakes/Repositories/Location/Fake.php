<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location;

use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use Illuminate\Support\Facades\Facade;

class Fake extends Facade
{
    public static function fake()
    {
        static::swap($fake = new Eloquent());

        return $fake;
    }

    protected static function getFacadeAccessor()
    {
        return LocationProcessorRepository::class;
    }
}
