<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands\Monorepo;


use Laravel\VaporCli\Commands\Command;
use Laravel\VaporCli\GitIgnore;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class DeleteReviewAppCommand extends Command
{

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $environment = $this->argument('environment');

        if ( ! is_null($this->vapor->environmentNamed(Manifest::id(), $environment))) {
            $this->vapor->deleteEnvironment(
                    Manifest::id(),
                    $environment,
                    );
        }

        Manifest::deleteEnvironment($environment);


        if (file_exists("$environment.Dockerfile")) {
            unlink("$environment.Dockerfile");
        }

        GitIgnore::remove(['.env.' . $environment]);

        Helpers::info('Environment deleted successfully.');
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('delete-review-app')
            ->addArgument('environment', InputArgument::REQUIRED, 'The review app name')
            ->setDescription('Delete a review app');
    }
}
