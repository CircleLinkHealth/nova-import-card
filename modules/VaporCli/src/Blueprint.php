<?php
/**
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli;


use Symfony\Component\Yaml\Yaml;

class Blueprint
{
    const VALID_BLUEPRINT_ENVS = [
        'staging',
        'provider',
    ];
    public string $environment;
    public string $blueprintEnv;
    public array $blueprintEnvVariables;
    public array $allApps;
    public string $app;
    public bool $optionDocker;

    public function __construct(string $environment, string $blueprint, string $allAppsString, bool $optionDocker)
    {
        $this->environment  = $environment;
        $this->blueprintEnv = $blueprint;
        $this->allApps      = explode(',', $allAppsString);
        $this->app          = array_reverse(explode('/', rtrim($_SERVER['PWD'], '-app')))[0];
        $this->manifest     = Manifest::current();

        if ( ! in_array($blueprint, self::VALID_BLUEPRINT_ENVS)) {
            throw new \Exception("Input blueprint '$blueprint' is invalid.");
        }
    }


    public function getEnvironmentVariables() : string
    {
        $vars = collect(
            $this->blueprintEnvVariables
        )
            ->mapWithKeys(function ($item) {
                $vars = explode('=', $item);

                return [trim($vars[0]) => trim($vars[1])];
            })
            ->merge(
                collect(self::defaultReviewEnvironmentVars())
            );


        if ($this->shouldGenerateUrlFor('superadmin')) {
            $vars['CPM_ADMIN_APP_URL'] = "https://superadmin-$this->environment.clh-$this->blueprintEnv.com";
        } else {
            $vars['CPM_ADMIN_APP_URL'] = $this->getBlueprintUrlForApp('superadmin');
        }

        if ($this->shouldGenerateUrlFor('provider')) {
            $vars['CPM_PROVIDER_APP_URL'] = "https://provider-$this->environment.clh-$this->blueprintEnv.com";
        } else {
            $vars['CPM_PROVIDER_APP_URL'] = $this->getBlueprintUrlForApp('provider');
        }


        return $vars->transform(function ($value, $key) {
            return "$key=$value";
        })
                           ->implode("\n");
    }

    public function getEnvironmentConfig(): array
    {
        $envConfig           = $this->manifest['environments'][$this->blueprintEnv];
        $envConfig['domain'] = str_replace($this->app, "$this->app-$this->environment", $envConfig['domain']);

        foreach ($envConfig['queues'] as $key => $name) {
            $envConfig['queues'][$key] = str_replace($this->app, "$this->app-$this->environment", $name);
        }


        if ($this->optionDocker) {
            $envConfig['runtime'] = 'docker';
        }

        return $envConfig;
    }

    public function getProjectId(): int
    {
        return $this->manifest['id'];
    }

    public function ensureDeployS3EnvExists()
    {
        $exists = file_exists("$this->blueprintEnv-deploy-s3.env");

        if ($exists) {
            return;
        }

        if ($this->blueprintEnv === 'production') {
            throw new \Exception("Please make sure that $this->app-app has production-deploy-s3.env available with the right credentials.");
        }

        file_put_contents(
            Path::current() . "/staging-deploy-s3.env",
            $this->defaultStagingS3Vars()
        );
    }

    public function defaultStagingS3Vars(): string
    {
        return "S3_SECRETS_SECRET=1QfZhQDi8Ihxh67VY4Pk69Sx1vsWefZfjLf9+K/v
S3_SECRETS_BUCKET=cpm-staging-keys
S3_SECRETS_KEY=AKIAZYB3F7ZGBKRUHG5Y
S3_SECRETS_REGION=us-east-1
ENV_TYPE=staging
APP_NAME=$this->app";
    }

    protected function defaultReviewEnvironmentVars(): array
    {
        return [
            "APP_DEBUG"           => "true",
            "CACHE_DRIVER"        => "array",
            "MAIL_DRIVER"         => "smtp",
            "MAIL_MAILER"         => "postmark",
            "LOW_CPM_QUEUE_NAME"  => "$this->app-$this->environment-low-$this->blueprintEnv",
            "HIGH_CPM_QUEUE_NAME" => "$this->app-$this->environment-high-$this->blueprintEnv",
            "REVISIONABLE_QUEUE"  => "$this->app-$this->environment-revisionable-$this->blueprintEnv",
            "APP_URL"             => "https://$this->app-$this->environment.clh-$this->blueprintEnv.com",
            "SESSION_DOMAIN"      => "$this->app-$this->environment.clh-$this->blueprintEnv.com",
            "SCOUT_MONITOR"       => "false",
            "UNIQUE_ENV_NAME"     => $this->environment,
        ];
    }

    public function setBlueprintEnvVariables(string $environmentVariables): void
    {
        $this->blueprintEnvVariables = explode("\n", $environmentVariables);
    }

    private function shouldGenerateUrlFor(string $app): bool
    {
        return in_array($app, $this->allApps) && $this->app !== $app;
    }

    private function getBlueprintUrlForApp(string $app): string
    {
        $superadminYaml = Yaml::parse(file_get_contents(str_replace($this->app, 'superadmin',
                getcwd()) . '/vapor.yml'));

        return $superadminYaml['environments'][$this->blueprintEnv]['domain'];
    }
}
