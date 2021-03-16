<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands\Monorepo;

use Laravel\VaporCli\Commands\Command;
use Laravel\VaporCli\Commands\DisplaysDeploymentProgress;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Path;
use Symfony\Component\Process\Process;

abstract class InParallelCommand extends Command
{
    use DisplaysDeploymentProgress;

    const CPM_APPS = [
        'awv',
        'caller',
        'provider',
        'self-enrollment',
        'superadmin',
    ];

    /**
     * @var Process[]
     */
    protected $activeProcesses = [];
    /**
     * @var Process[]
     */
    protected $allProcesses = [];
    protected $endTimes     = [];
    protected $startTimes   = [];

    abstract public function createProcess(string $app): Process;

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $start = microtime(true);
        foreach ($this->argument('apps') as $app) {
            Helpers::line("Preparing[$app]");
            $process = $this->createProcess($app);
            Helpers::line("Running[$app] ".$process->getCommandLine());
            $process->start();

            $this->allProcesses[$app]    = $process;
            $this->activeProcesses[$app] = $process;
            $this->startTimes[$app]      = microtime(true);
        }

        Helpers::line(sprintf('Running %d processes in parallel', count($this->activeProcesses)));

        while (count($this->activeProcesses)) {
            foreach ($this->activeProcesses as $appName => $runningProcess) {
                if ( ! $runningProcess->isRunning()) {
                    $this->endTimes[$appName] = microtime(true);
                    Helpers::line(
                        "Done[$appName] ".$runningProcess->getCommandLine()
                    );
                    Helpers::danger($runningProcess->getErrorOutput());
                    unset($this->activeProcesses[$appName]);
                    continue;
                }

                if ($incrOutput = $runningProcess->getIncrementalOutput()) {
                    Helpers::line("Status[$appName] $incrOutput");
                    continue;
                }
            }
            sleep(1);
        }

        $this->reportFinishedProcesses();

        $end = microtime(true);

        $totalTimeInSeconds = $end - $start;

        Helpers::line("It took $totalTimeInSeconds seconds for all processes to finish.");
    }

    protected function appPath(string $app)
    {
        return Path::current()."/apps/$app-app";
    }

    protected function reportFinishedProcesses(): void
    {
        foreach ($this->allProcesses as $appName => $process) {
            $end                = $this->endTimes[$appName];
            $start              = $this->startTimes[$appName];
            $totalTimeInSeconds = $end - $start;
            Helpers::line("Process[$appName] duration: $totalTimeInSeconds seconds.");

            if ( ! $process->isSuccessful()) {
                $output = empty($process->getErrorOutput()) ? $process->getOutput() : $process->getErrorOutput();
                Helpers::danger("Fail[$appName] ".$output);
                continue;
            }

            Helpers::line("Success[$appName] ".$process->getCommandLine());
        }
    }
}
