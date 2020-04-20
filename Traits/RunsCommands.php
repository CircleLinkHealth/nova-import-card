<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Traits;

use Symfony\Component\Process\Process;

trait RunsCommands
{
    /**
     * Run a command on the terminal.
     *
     * @throws \Exception
     *
     * @return Process
     */
    private function runCpmCommand(string $command)
    {
        $this->info("Running: `$command`");
        $process = Process::fromShellCommandline($command);
        $process->run();

        if ( ! $process->isSuccessful()) {
            throw new \Exception('Failed to execute process.'.$process->getIncrementalErrorOutput());
        }

        $errors = $process->getErrorOutput();

        if ( ! empty($errors)) {
            $this->info("Errors `{$errors}`");
            \Log::debug('Errors: '.$errors, ['file' => __FILE__, 'line' => __LINE__]);
        }

        $output = $process->getOutput();

        if ($output) {
            $this->info('Output: '.$output);
            \Log::debug('Output: '.$output, ['file' => __FILE__, 'line' => __LINE__]);
        }

        return $process;
    }
}
