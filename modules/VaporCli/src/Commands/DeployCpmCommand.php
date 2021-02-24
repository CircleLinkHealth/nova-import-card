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
        'self-enrollment'
    ];

    /**
     * @var Process[]
     */
    private $activeProcesses = [];

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
            ->addOption('message', null, InputOption::VALUE_OPTIONAL, 'The message for the commit that is being deployed')
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
        foreach ($this->argument('apps') as $app) {
            Helpers::line("Preparing $app");
            $process = $this->createDeployProcess($app, $this->argument('environment'), $this->argument('environment_type'));
            Helpers::line('Running: ' . $process->getCommandLine());
            $process->start();

            $this->activeProcesses[] = $process;
        }

        Helpers::line(sprintf('Deploying %d apps asynchronously', count($this->activeProcesses)));

        while (count($this->activeProcesses)) {
            foreach ($this->activeProcesses as $i => $runningProcess) {
                if (! $runningProcess->isRunning()) {
                    Helpers::line('Finished: '.$runningProcess->getWorkingDirectory().$runningProcess->getCommandLine());
                    Helpers::danger($runningProcess->getErrorOutput());
                    unset($this->activeProcesses[$i]);
                    continue;
                }

            }

            // check every second
            sleep(1);
        }

        $this->reportFinishedProcesses();
    }

    private function reportFinishedProcesses(): void
    {
        foreach ($this->activeProcesses as $process) {
            if (! $process->isSuccessful()) {
                Helpers::danger($process->getErrorOutput());
                continue;
            }

            Helpers::line($process->getCommandLine());
        }
    }

    private function createDeployProcess(
        string $app,
        string $envName,
        string $envType
    ): Process {
        return new Process([
            Path::current().'/modules/VaporCli/vapor',
            'deploy',
            $envName,
            $envType
                           ], Path::current()."/apps/$app-app", null, null, null);
    }
}
