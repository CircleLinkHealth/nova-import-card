<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests;

use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    public function refreshDatabase()
    {
        if ($this->usingInMemoryDatabase()) {
            $this->refreshInMemoryDatabase();

            $this->seedDatabase();

            return;
        }

        /*
         * Uncomment below to refresh and seed a conventional database
         *
         * NOTE: If you're using multiple processes in paratest to run the test suite, and the database for the test suite is mysql, leave below commented out.
         */
//        $this->refreshTestDatabase();
//        $this->seedDatabase();

        //Since we have commented out $this->refreshTestDatabase()
        //Adding this to rollback transactions at the end of each test
        $this->beginDatabaseTransaction();
    }

    public function seedDatabase()
    {
        Artisan::call('db:seed');

        Artisan::call('db:seed', [
            '--class' => 'TestSuiteSeeder',
        ]);
    }
}
