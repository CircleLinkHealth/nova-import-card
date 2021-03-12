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
use Laravel\VaporCli\Path;
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
        $blueprintEnv = 'staging';

        Helpers::ensure_api_token_is_available();

        $manifest = Manifest::current();

        $environment = $this->argument('environment');
        $app = array_reverse(explode('/', rtrim($_SERVER['PWD'], '-app')))[0];

        if (isset($manifest['environments']['staging'])) {
            $envConfig = $manifest['environments'][$blueprintEnv];
            $envConfig['domain'] = str_replace($app, "$app-$environment", $envConfig['domain']);

            foreach ($envConfig['queues'] as $key => $name) {
                $envConfig['queues'][$key] = str_replace($app, "$app-$environment", $name);
            }
        }

        if ($this->option('docker')) {
            $envConfig['runtime'] = 'docker';
        }

        $projectId = Manifest::id();

        if (is_null($this->vapor->environmentNamed($projectId, $environment)))
        {
            $this->vapor->createEnvironment(
                Manifest::id(),
                $environment,
                $this->option('docker')
            );
        }


        if (! isset(Manifest::current()['environments'][$environment])){
            Manifest::addEnvironment(
                $environment,
                $envConfig
            );
        }

        if ($this->option('docker')) {
            if (file_exists("$blueprintEnv.Dockerfile")) {
                copy("$blueprintEnv.Dockerfile", "$environment.Dockerfile");
            } else {
                Dockerfile::fresh($environment);
            }
        }

        if (! file_exists("staging-deploy-s3.env")){
            file_put_contents(
                Path::current()."/staging-deploy-s3.env",
                "S3_SECRETS_SECRET=1QfZhQDi8Ihxh67VY4Pk69Sx1vsWefZfjLf9+K/v
S3_SECRETS_BUCKET=cpm-staging-keys
S3_SECRETS_KEY=AKIAZYB3F7ZGBKRUHG5Y
S3_SECRETS_REGION=us-east-1
ENV_TYPE=staging
APP_NAME=$app"
            );
        }

        //check which apps are created - if admin exists make sure to add the correct url in env. Same with provider
        //create .env file and upload vars to vapor

        //use this
//        public function updateEnvironmentVariables($projectId, $environment, $variables)


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
