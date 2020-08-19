<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient;

use CircleLinkHealth\CcmBilling\Contracts\PatientProcessorEloquentRepository;
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
        return PatientProcessorEloquentRepository::class;
    }
}
