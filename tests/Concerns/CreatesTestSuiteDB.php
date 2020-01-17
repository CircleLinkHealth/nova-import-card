<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Concerns;

use App\Traits\RunsConsoleCommands;

trait CreatesTestSuiteDB
{
    use RunsConsoleCommands;

    public function createDB(?string $dbName = 'cpm_tests')
    {
        if ('cpm_production' === $dbName) {
            abort('It is not recommended to run this command on the production database');
        }

        if (getenv('CI')) {
            $dbName = getenv('HEROKU_TEST_RUN_ID');
        }

        $this->createDatabase($dbName ?? 'cpm_tests');
    }

    private function createDatabase(string $dbName)
    {
        $migrateInstallCommand  = $this->runCommand(['php', 'artisan', '-vvv', 'mysql:createdb', $dbName, '--env=testing']);
        $migrateCommand         = $this->runCommand(['php', 'artisan', '-vvv', 'migrate:fresh', '--env=testing']);
        $migrateCommand         = $this->runCommand(['php', 'artisan', '-vvv', 'migrate:views', '--env=testing']);
        $testSuiteSeederCommand = $this->runCommand(['php', 'artisan', '-vvv', 'db:seed', '--class=TestSuiteSeeder', '--env=testing']);
    }
}
