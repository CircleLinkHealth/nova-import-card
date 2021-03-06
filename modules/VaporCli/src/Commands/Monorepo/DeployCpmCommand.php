<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands\Monorepo;

use Laravel\VaporCli\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class DeployCpmCommand extends InParallelCommand
{
    public function createProcess(string $app): Process
    {
        return new Process(
            [
                Path::current().'/modules/VaporCli/vapor',
                'deploy',
                $this->argument('environment'),
                $this->argument('environment_type'),
            ],
            $this->appPath($app),
            null,
            null,
            null
        );
    }

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
            ->addArgument('apps', InputArgument::IS_ARRAY, 'The apps to run the command for', self::CPM_APPS)
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
}
