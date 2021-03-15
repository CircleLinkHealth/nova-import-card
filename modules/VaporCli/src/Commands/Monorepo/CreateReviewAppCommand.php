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
        $blueprintEnv = $this->argument('blueprint-env');

        if (! in_array($blueprintEnv, [
            'production',
            'staging'
        ])){
            Helpers::danger('Invalid blueprint environment.');
            return;
        }

        Helpers::ensure_api_token_is_available();

        $manifest = Manifest::current();

        $environment = $this->argument('environment');
        $app         = array_reverse(explode('/', rtrim($_SERVER['PWD'], '-app')))[0];

        if (isset($manifest['environments'][$blueprintEnv])) {
            $envConfig           = $manifest['environments'][$blueprintEnv];
            $envConfig['domain'] = str_replace($app, "$app-$environment", $envConfig['domain']);

            foreach ($envConfig['queues'] as $key => $name) {
                $envConfig['queues'][$key] = str_replace($app, "$app-$environment", $name);
            }
        }

        if ($this->option('docker')) {
            $envConfig['runtime'] = 'docker';
        }

        $projectId = Manifest::id();

        if (is_null($this->vapor->environmentNamed($projectId, $environment))) {
            $this->vapor->createEnvironment(
                Manifest::id(),
                $environment,
                $this->option('docker')
            );
        }

        if ( ! isset(Manifest::current()['environments'][$environment])) {
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

        if ( ! file_exists("staging-deploy-s3.env")) {
            file_put_contents(
                Path::current() . "/staging-deploy-s3.env",
                "S3_SECRETS_SECRET=1QfZhQDi8Ihxh67VY4Pk69Sx1vsWefZfjLf9+K/v
S3_SECRETS_BUCKET=cpm-staging-keys
S3_SECRETS_KEY=AKIAZYB3F7ZGBKRUHG5Y
S3_SECRETS_REGION=us-east-1
ENV_TYPE=staging
APP_NAME=$app"
            );
        }

        $vars = collect(
            explode("\n", $this->vapor->environmentVariables($projectId, $blueprintEnv))
        )
            ->mapWithKeys(function ($item) {
                $vars = explode('=', $item);

                return [$vars[0] => $vars[1]];
            })
            ->merge(
                collect(self::defaultReviewEnvironmentVars($app, $environment, $blueprintEnv))
            );


        $allAppsArray = explode(',', $this->argument('allApps'));

        if ($app !== 'superadmin') {
            if (in_array('superadmin', $allAppsArray)) {
                $vars['CPM_ADMIN_APP_URL'] = "https://superadmin-$environment.clh-$blueprintEnv.com";
            } else {
                $superadminYaml = Yaml::parse(file_get_contents(str_replace($app, 'superadmin',
                        getcwd()) . '/vapor.yml'));
                //if not staging get production
                $vars['CPM_ADMIN_APP_URL'] = $superadminYaml['environments'][$blueprintEnv];
            }

        }

        if ($app !== 'provider') {
            if (in_array('superadmin', $allAppsArray)) {
                $vars['CPM_PROVIDER_APP_URL'] = "https://provider-$environment.clh-$blueprintEnv.com";
            } else {
                $providerYaml = Yaml::parse(file_get_contents(str_replace($app, 'provider', getcwd()) . '/vapor.yml'));
                //if not staging get production
                $vars['CPM_PROVIDER_APP_URL'] = $superadminYaml['environments'][$blueprintEnv];
            }

        }

        $varsString = $vars->transform(function ($value, $key) {
            return "$key=$value";
        })
                           ->implode("\n");

        $this->vapor->updateEnvironmentVariables(
            $projectId,
            $environment,
            $varsString
        );

        GitIgnore::add(['.env.' . $environment]);

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

    protected static function defaultReviewEnvironmentVars(string $app, string $environment, string $blueprintEnv): array
    {
        return [
            "APP_DEBUG"           => "true",
            "CACHE_DRIVER"        => "array",
            "MAIL_DRIVER"         => "smtp",
            "MAIL_MAILER"         => "postmark",
            "LOW_CPM_QUEUE_NAME"  => "$app-$environment-low-$blueprintEnv",
            "HIGH_CPM_QUEUE_NAME" => "$app-$environment-high-$blueprintEnv",
            "REVISIONABLE_QUEUE"  => "$app-$environment-revisionable-$blueprintEnv",
            "APP_URL"             => "https://$app-$environment.clh-$blueprintEnv.com",
            "SESSION_DOMAIN"      => "$app-$environment.clh-$blueprintEnv.com",
            "SCOUT_MONITOR"       => "false",
            "UNIQUE_ENV_NAME"     => $environment,
        ];
    }
}
