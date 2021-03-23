<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands\Monorepo;

use Laravel\VaporCli\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

class DeleteReviewAppsCommand extends InParallelCommand
{
    public function createProcess(string $app): Process
    {
        $args = [
            Path::current().'/modules/VaporCli/vapor',
            'delete-review-app',
            $this->argument('environment'),
        ];

        return new Process(
            $args,
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
            ->setName('delete-review-apps')
            ->addArgument('environment', InputArgument::REQUIRED, 'The review app name')
            ->addArgument('apps', InputArgument::IS_ARRAY, 'The apps to run the command for', self::CPM_APPS)
            ->setDescription('Delete a review environment for the given apps');
    }
}
