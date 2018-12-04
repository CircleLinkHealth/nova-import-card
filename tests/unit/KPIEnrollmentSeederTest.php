<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class KPIEnrollmentSeederTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     */
    public function test_example()
    {
        Artisan::call('db:seed', [
            '--class' => 'KPIEnrollmentSeeder',
        ]);

        $this->assertTrue(true);
    }
}
