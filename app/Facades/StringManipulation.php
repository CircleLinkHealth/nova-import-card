<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class StringManipulation extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'stringManipulation';
    }
}
