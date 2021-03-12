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

        $blueprintEnvVars = $this->vapor->environmentVariables($projectId, $blueprintEnv);
        $vars = collect(explode("\n", $blueprintEnvVars))
            ->mapWithKeys(function($item){
                $vars = explode('=',$item);
                return [$vars[0] => $vars[1]];
            })
        ->merge(
            collect(self::defaultReviewEnvironmentVars($app, $environment))
        )->transform(function ($value, $key){
            return "$key=$value";
            })
        ->implode("\n");

        $this->vapor->updateEnvironmentVariables($projectId, $environment, $vars);

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

    protected static function defaultReviewEnvironmentVars(string $app, string $environment): array
    {
        return [
            "APP_DEBUG"=> "true",
            "CACHE_DRIVER" => "array",
            "MAIL_DRIVER" => "smtp",
            "MAIL_MAILER" => "postmark",
            "LOW_CPM_QUEUE_NAME"=> $app."-low-".$environment,
            "HIGH_CPM_QUEUE_NAME"=> $app."-low-".$environment,
            "REVISIONABLE_QUEUE" => $app."-revisionable-".$environment,
            "APP_URL"=> "https://$app-$environment.clh-staging.com",
            "SESSION_DOMAIN" => "$app-$environment.clh-staging.com",
            "SCOUT_MONITOR" => "false",
            "UNIQUE_ENV_NAME" => $environment
        ];
    }
}
