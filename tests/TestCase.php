<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\MigrateAndSeedOnce;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use MigrateAndSeedOnce;
}
