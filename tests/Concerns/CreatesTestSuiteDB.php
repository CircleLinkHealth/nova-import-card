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

        if ($isCi = getenv('CI')) {
            $dbName = getenv('HEROKU_TEST_RUN_ID');
        }

        $this->createDatabase($dbName ?? 'cpm_tests', ! $isCi);
    }

    private function createDatabase(string $dbName, $isLocal = true)
    {
        collect(
            [
                ['mysql:createdb', $dbName],
            ]
        )->each(
            function ($command) use ($isLocal) {
                $base = ['php', 'artisan', '-vvv'];
                if ($isLocal) {
                    array_push($command, '--env=testing');
                }

                $this->runCommand(array_merge($base, $command));
            }
        );
    }
}
