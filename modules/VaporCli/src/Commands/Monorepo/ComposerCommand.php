<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands\Monorepo;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

class ComposerCommand extends InParallelCommand
{
    public function createProcess(string $app): Process
    {
        return new Process(
            array_merge(
                [
                    'composer',
                ],
                explode(' ', $this->argument('directive'))
            ),
            $this->appPath($app),
            [
                'COMPOSER_MEMORY_LIMIT' => -1,
            ],
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
            ->setName('composer')
            ->addArgument('directive', InputArgument::REQUIRED, 'Composer directive to run')
            ->addArgument('apps', InputArgument::IS_ARRAY, 'The apps to run the command for', self::CPM_APPS)
            ->setDescription('Deploy all CPM apps');
    }
}
