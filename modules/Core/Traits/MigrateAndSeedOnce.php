<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Traits;

use Symfony\Component\Process\Process;

trait MigrateAndSeedOnce
{
    /**
     * If true, setup has run at least once.
     *
     * @var bool
     */
    protected static $setUpHasRunOnce = false;

    /**
     * After the first run of setUp "migrate:fresh --seed".
     */
    protected function setUp(): void
    {
        parent::setUp();

        if ( ! static::$setUpHasRunOnce) {
            $this->createDatabase();
            static::$setUpHasRunOnce = true;
        }
    }

    private function createDatabase()
    {
        //fixme: do not create a new database, just copy from $dbPath (it should be created in SqliteRunnerCLH)
        $dbPath = \Config::get('database.connections.sqlite.database');

        if ( ! file_exists($dbPath)) {
            touch($dbPath);
        }

        if (false !== getenv('TEST_TOKEN')) {
            $dbname = 'testdb_'.getenv('TEST_TOKEN');
        } else {
            $dbname = 'testdb';
        }

        chdir(base_path());

        $migrateCommand         = $this->runCommand(['php', 'artisan', '-vvv', 'migrate:fresh']);
        $seedCommand            = $this->runCommand(['php', 'artisan', '-vvv', 'db:seed']);
        $testSuiteSeederCommand = $this->runCommand(['php', 'artisan', '-vvv', 'db:seed', '--class=TestSuiteSeeder']);
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
