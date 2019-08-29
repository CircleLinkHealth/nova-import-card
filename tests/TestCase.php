<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests;

use Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Fluent;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->hotfixSqlite();
    }

    public function hotfixSqlite()
    {
        \Illuminate\Database\Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
            return new class($connection, $database, $prefix, $config) extends SQLiteConnection {
                public function getSchemaBuilder()
                {
                    if (null === $this->schemaGrammar) {
                        $this->useDefaultSchemaGrammar();
                    }

                    return new class($this) extends SQLiteBuilder {
                        protected function createBlueprint($table, \Closure $callback = null)
                        {
                            return new class($table, $callback) extends Blueprint {
                                public function dropForeign($index)
                                {
                                    return new Fluent();
                                }
                            };
                        }
                    };
                }
            };
        });
    }

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
