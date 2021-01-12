<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Console\Commands;

use CircleLinkHealth\Core\Traits\CreatesTestSuiteDB;
use Illuminate\Console\Command;

class CreateAndSeedTestSuiteDB extends Command
{
    use CreatesTestSuiteDB;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an SQLite database for the test suite.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:prepare-test_suite-db {dbName?}';

    public function handle()
    {
        $this->warn('Creating and seeding test DB.'.PHP_EOL);
        $start = microtime(true);

        $dbName = $this->argument('dbName');
        $this->createDB($dbName);

        $duration = round((microtime(true) - $start) * 1000);

        $this->line(PHP_EOL.PHP_EOL.PHP_EOL."Time: {$duration}ms");
    }
}
