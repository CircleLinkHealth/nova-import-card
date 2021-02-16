<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Traits;

use Illuminate\Support\Collection;

trait Cacheable
{
    private static ?Collection $cached = null;

    public static function cached()
    {
        if ( ! self::$cached) {
            self::$cached = self::all();
        }

        return self::$cached;
    }
}
