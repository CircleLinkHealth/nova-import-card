<?php

namespace Laravel\VaporCli\Commands;

use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class DeployCpmCommand extends Command
{
    use DisplaysDeploymentProgress;

    const CPM_APPS = [
        'admin',
        'awv',
        'caller',
        'provider',
        'self-enrollment',
    ];

    /**
     * @var Process[]
     */
    private $activeProcesses = []; /**
     * @var Process[]
     */
    private $allProcesses = [];
    private $startTimes = [];
    private $endTimes = [];

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('deploy:cpm')
            ->addArgument('environment', InputArgument::REQUIRED, 'The environment name')
            ->addArgument('environment_type', InputArgument::REQUIRED, 'The environment type')
            ->addArgument('apps', InputArgument::IS_ARRAY, 'The environment type', self::CPM_APPS)
            ->addOption('commit', null, InputOption::VALUE_OPTIONAL, 'The commit hash that is being deployed')
            ->addOption(
                'message',
                null,
                InputOption::VALUE_OPTIONAL,
                'The message for the commit that is being deployed'
            )
            ->addOption('without-waiting', null, InputOption::VALUE_NONE, 'Deploy without waiting for progress')
            ->setDescription('Deploy all CPM apps');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $start = microtime(true);
        foreach ($this->argument('apps') as $app) {
            Helpers::line("Preparing $app");
            $process = $this->createDeployProcess(
                $app,
                $this->argument('environment'),
                $this->argument('environment_type')
            );
            Helpers::line('Running: '.$process->getCommandLine());
            $process->start();

            $this->allProcesses[$app] = $process;
            $this->activeProcesses[$app] = $process;
            $this->startTimes[$app] = microtime(true);
        }

        Helpers::line(sprintf('Running %d processes in parallel', count($this->activeProcesses)));

        while (count($this->activeProcesses)) {
            foreach ($this->activeProcesses as $appName => $runningProcess) {
                if ( ! $runningProcess->isRunning()) {
                    $this->endTimes[$app] = microtime(true);
                    Helpers::line(
                        'Finished: '.$appName.$runningProcess->getCommandLine()
                    );
                    Helpers::danger($runningProcess->getErrorOutput());
                    unset($this->activeProcesses[$appName]);
                    continue;
                }

                if ($incrOutput = $runningProcess->getIncrementalOutput()) {
                    Helpers::line($appName.$incrOutput);
                }
            }
            sleep(5);
        }

        $this->reportFinishedProcesses();

        $end = microtime(true);

        $totalTimeInSeconds = $end-$start;

        Helpers::line("It took $totalTimeInSeconds seconds for all processes to finish.");
    }

    private function reportFinishedProcesses(): void
    {
        foreach ($this->allProcesses as $appName => $process) {
            $totalTimeInSeconds = $this->endTimes[$appName] - $this->startTimes[$appName];
            Helpers::line("Process[$appName] duration: $totalTimeInSeconds seconds.");

            if ( ! $process->isSuccessful()) {
                Helpers::danger('Failed: '.$appName. $process->getErrorOutput());
                continue;
            }

            Helpers::line('Successful: '.$appName. $process->getCommandLine());
        }
    }

    private function createDeployProcess(
        string $app,
        string $envName,
        string $envType
    ): Process {
        return new Process(
            [
                Path::current().'/modules/VaporCli/vapor',
                'deploy',
                $envName,
                $envType,
            ], Path::current()."/apps/$app-app", null, null, null
        );
    }
}
