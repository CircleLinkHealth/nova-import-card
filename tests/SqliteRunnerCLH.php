<?php

declare(strict_types=1);

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace ParaTest\Runners\PHPUnit;

use Symfony\Component\Process\Process;

class SqliteRunnerCLH extends SqliteRunner
{
    public function __construct(array $opts = [])
    {
        parent::__construct($opts);
        try {
            $config = include './config/database.php';
            $this->createDatabase($config['connections']['sqlite']['database']);
        } catch (\Exception $e) {
        }
    }

    private function createDatabase($dbPath)
    {
        if ( ! file_exists($dbPath)) {
            touch($dbPath);
        }

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
