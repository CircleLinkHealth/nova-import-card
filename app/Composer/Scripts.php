<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Composer;

use Symfony\Component\Process\Process;

class Scripts
{
    public static function postDeploy()
    {
        $env = getenv('APP_ENV');

        if (in_array($env, ['local', 'testing'])) {
            echo "Not running because env is $env. \n\n";

            return;
        }

        $static = new static();

        $static->runCommand(['php', 'artisan', '-vvv', 'migrate', '--force']);
        $static->runCommand(['php', 'artisan', '-vvv', 'deploy:post']);
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
