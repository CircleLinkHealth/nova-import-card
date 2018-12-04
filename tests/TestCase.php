<?php

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

        /**
         * Uncomment below to refresh and seed a conventional database
         *
         * NOTE: If you're using paratest to run the test suite, and mysql, leave below commented out.
         */
//        $this->refreshTestDatabase();
//        $this->seedDatabase();
    }

    public function seedDatabase()
    {
        Artisan::call('db:seed');

        Artisan::call('db:seed', [
            '--class' => 'TestSuiteSeeder'
        ]);
    }
}
