<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands\Monorepo;

use Laravel\VaporCli\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class CreateReviewAppsCommand extends InParallelCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('review-apps')
            ->addArgument('environment', InputArgument::REQUIRED, 'The review app name')
            ->addArgument('apps', InputArgument::IS_ARRAY, 'The apps to run the command for', self::CPM_APPS)
            ->addOption('docker', null, InputOption::VALUE_NONE, 'Indicate that the environment will use Docker images as its runtime')
            ->setDescription('Create a new review app');
    }

    public function createProcess(string $app): Process
    {
        $args = [
            Path::current().'/modules/VaporCli/vapor',
            'review-app',
            $this->argument('environment'),
        ];

        if ($this->option('docker')) {
            $args[] = '--docker';
        }
        return new Process(
            $args,
            $this->appPath($app),
            null,
            null,
            null
        );
    }
}
