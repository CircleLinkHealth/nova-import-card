<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Traits;

use Symfony\Component\Process\Process;

trait RunsConsoleCommands
{
    /**
     * @throws \Exception
     *
     * @return Process
     */
    public function runCpmCommand(array $command, bool $echoOutput = true)
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
