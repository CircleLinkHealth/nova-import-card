<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;

    protected function sanitizeString($string){
        return filter_var($string, FILTER_SANITIZE_STRING);
    }
}
