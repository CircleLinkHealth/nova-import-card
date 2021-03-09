<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands\Monorepo;

use Laravel\VaporCli\Commands\Command;
use Laravel\VaporCli\Dockerfile;
use Laravel\VaporCli\GitIgnore;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateReviewAppCommand extends Command
{
    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $manifest = Manifest::current();

        $environment = $this->argument('environment');

        if (isset($manifest['environments']['staging'])) {
            $app = array_reverse(explode('/', rtrim($_SERVER['PWD'], '-app')))[0];
            $envConfig = $manifest['environments']['staging'];
            $envConfig['domain'] = str_replace($app, "$app-$environment", $envConfig['domain']);

            foreach ($envConfig['queues'] as $key => $name) {
                $envConfig['queues'][$key] = str_replace($app, "$app-$environment", $name);
            }
        }

        if ($this->option('docker')) {
            $envConfig['runtime'] = 'docker';
        }

        $this->vapor->createEnvironment(
            Manifest::id(),
            $environment,
            $this->option('docker')
        );

        Manifest::addEnvironment(
            $environment,
            $envConfig
        );

        if ($this->option('docker')) {
            Dockerfile::fresh($environment);
        }

        GitIgnore::add(['.env.'.$environment]);

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
            ->addOption('docker', null, InputOption::VALUE_NONE, 'Indicate that the environment will use Docker images as its runtime')
            ->setDescription('Create a new review app');
    }
}
