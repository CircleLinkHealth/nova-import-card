<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Concerns;

use Symfony\Component\Process\Process;
use Tests\CreatesApplication;

trait CreatesSqliteDB
{
    use CreatesApplication;

    public function createDB($dbNameSuffix = '')
    {
        $app = $this->createApplication();

        if ( ! empty($dbNameSuffix)) {
            $dbNameSuffix = "-$dbNameSuffix";
        }

        $dbname = base_path("tests/data/sqlite/test_db$dbNameSuffix.sqlite");

        $this->createDatabase($dbname);
    }

    private function createDatabase($dbPath)
    {
        if ( ! file_exists($dbPath)) {
            touch($dbPath);
        }

        $migrateCommand         = $this->runCommand(['php', 'artisan', '-vvv', 'migrate:fresh', '--env=testing']);
        $testSuiteSeederCommand = $this->runCommand(['php', 'artisan', '-vvv', 'db:seed', '--class=TestSuiteSeeder', '--env=testing']);
    }

    private function runCommand(array $command, bool $echoOutput = true)
    {
        $process = new Process($command);

        echo PHP_EOL.'Running command:';

        foreach ($command as $c) {
            echo " $c ";
        }

        echo PHP_EOL;

        $process->run();

        $output = (string) trim($process->getOutput());

        if (true === $echoOutput) {
            echo $output;
        }

        if (0 !== $process->getExitCode()) {
            echo $process->getErrorOutput();
            throw new \Exception($process->getExitCodeText().' when executing '.$process->getCommandLine());
        }

        return $process;
    }
}
