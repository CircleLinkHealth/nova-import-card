<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Patient;

use CircleLinkHealth\CcmBilling\Contracts\PatientServiceProcessorRepository;
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
        return PatientServiceProcessorRepository::class;
    }
}
