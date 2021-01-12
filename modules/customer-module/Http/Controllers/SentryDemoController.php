<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Http\Controllers;

class SentryDemoController
{
    public function throw()
    {
        throw new \Exception('Test exception from '.app()->environment());
    }
}
