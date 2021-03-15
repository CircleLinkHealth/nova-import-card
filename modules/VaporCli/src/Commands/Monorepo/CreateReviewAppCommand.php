<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands\Monorepo;

use Laravel\VaporCli\Blueprint;
use Laravel\VaporCli\Commands\Command;
use Laravel\VaporCli\Dockerfile;
use Laravel\VaporCli\GitIgnore;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

class CreateReviewAppCommand extends Command
{
    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $blueprint = new Blueprint(
            $this->argument('environment'),
            $this->argument('blueprint-env'),
            $this->argument('allApps'),
            $this->option('docker')
        );

        $projectId = $blueprint->getProjectId();
        Helpers::ensure_api_token_is_available();

        $blueprint->setBlueprintEnvVariables(
            $this->vapor->environmentVariables($projectId, $blueprint->blueprintEnv)
        );

        if (is_null($this->vapor->environmentNamed($projectId, $blueprint->environment))) {
            $this->vapor->createEnvironment(
                $projectId,
                $blueprint->environment,
                $blueprint->optionDocker
            );
        }

        if ( ! isset(Manifest::current()['environments'][$blueprint->environment])) {
            Manifest::addEnvironment(
                $blueprint->environment,
                $blueprint->getEnvironmentConfig()
            );
        }

        if ($blueprint->optionDocker) {
            if (file_exists("$blueprint->blueprintEnv.Dockerfile")) {
                copy("$blueprint->blueprintEnv.Dockerfile", "$blueprint->environment.Dockerfile");
            } else {
                Dockerfile::fresh($blueprint->environment);
            }
        }

        $blueprint->ensureDeployS3EnvExists();

        $this->vapor->updateEnvironmentVariables(
            $projectId,
            $blueprint->environment,
            $blueprint->getEnvironmentVariables()
        );

        GitIgnore::add(['.env.' . $blueprint->environment]);

        Helpers::info('Environment created successfully.');
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('review-app')
            ->addArgument('environment', InputArgument::REQUIRED, 'The review app name')
            ->addArgument('blueprint-env', InputArgument::REQUIRED, 'The blueprint env name. I.e. Staging or production')
            ->addArgument('allApps', InputArgument::REQUIRED, 'All apps that we are creating environment for')
            ->addOption('docker', null, InputOption::VALUE_NONE,
                'Indicate that the environment will use Docker images as its runtime')
            ->setDescription('Create a new review app');
    }
}
