<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\VaporCli\Aws\AwsStorageProvider;
use Laravel\VaporCli\Clipboard;
use Laravel\VaporCli\Docker;
use Laravel\VaporCli\Git;
use Laravel\VaporCli\Helpers;
use Laravel\VaporCli\Manifest;
use Laravel\VaporCli\Path;
use Laravel\VaporCli\ServeAssets;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeployCommand extends Command
{
    use DisplaysDeploymentProgress;

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        Helpers::ensure_api_token_is_available();

        $this->ensureManifestIsValid();

        // First we will build the project and create a new deployment artifact for the
        // project deployment. Once that has been done we can upload the assets into
        // storage so that they can be accessed publicly or displayed on the site.
        $this->serveAssets($artifact = $this->buildProject(
            $this->vapor->project(Manifest::id())
        ));

        (new Filesystem())->deleteDirectory(Path::vapor());

        $deployment = $this->handleCancellations($this->vapor->deploy(
            $artifact['id'],
            Manifest::current()
        ));

        if ($this->option('without-waiting')) {
            Helpers::line();

            return Helpers::info('Artifact uploaded successfully.');
        }

        $deployment = $this->displayDeploymentProgress($deployment);

        if ('failed' == $deployment['status']) {
            exit(1);
        }

        Clipboard::deployment($deployment);
    }

    /**
     * Get the proper asset domain for the given project.
     *
     *
     * @return string
     */
    protected function assetDomain(array $project)
    {
        if ($this->usesCloudFront() && 'deployed' == $project['cloudfront_status']) {
            return $project['asset_domains']['cloudfront'] ??
                    $project['asset_domains']['s3'];
        }

        return $project['asset_domains']['s3'];
    }

    /**
     * Build the project and create a new artifact for the deployment.
     *
     *
     * @return array
     */
    protected function buildProject(array $project)
    {
        $uuid = (string) Str::uuid();

        $this->call('build', [
            'environment'      => $this->argument('environment'),
            'environment_type' => $this->argument('environment_type'),
            '--asset-url'      => $this->assetDomain($project).'/'.$uuid,
        ]);

        return $this->uploadArtifact(
            $this->argument('environment'),
            $uuid
        );
    }

    /**
     * Attempt to cancel the given deployment.
     *
     *
     * @return void
     */
    protected function cancelDeployment(array $deployment)
    {
        $this->vapor->cancelDeployment($deployment['id']);

        Helpers::line();
        Helpers::danger('Attempting to cancel deployment...');

        $cancellingAt = Carbon::now();

        do {
            $deployment = $this->vapor->deployment($deployment['id']);

            if ($deployment['has_ended'] && 'cancelled' == $deployment['status']) {
                return Helpers::comment('Deployment cancelled successfully.');
            }
            if ($deployment['has_ended'] || Carbon::now()->subSeconds(10)->gte($cancellingAt)) {
                return Helpers::danger('Vapor was unable to cancel the deployment.');
            }

            sleep(3);
        } while ( ! $deployment['has_ended']);
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('deploy')
            ->addArgument('environment', InputArgument::REQUIRED, 'The environment name')
            ->addArgument('environment_type', InputArgument::REQUIRED, 'The environment type')
            ->addOption('commit', null, InputOption::VALUE_OPTIONAL, 'The commit hash that is being deployed')
            ->addOption('message', null, InputOption::VALUE_OPTIONAL, 'The message for the commit that is being deployed')
            ->addOption('without-waiting', null, InputOption::VALUE_NONE, 'Deploy without waiting for progress')
            ->setDescription('Deploy an environment');
    }

    /**
     * Create a hash for the vendor directory.
     *
     * @return string
     */
    protected function createVendorHash()
    {
        return md5(md5_file(Path::app().'/composer.json').md5_file(Path::app().'/composer.lock').md5_file(Path::vendor().'/composer/installed.json').md5_file(Path::vendor().'/composer/autoload_real.php'));
    }

    /**
     * Ensure the current manifest is valid.
     *
     * @return void
     */
    protected function ensureManifestIsValid()
    {
        $this->vapor->validateManifest(
            Manifest::id(),
            $this->argument('environment'),
            Manifest::current(),
            $this->getCliVersion(),
            $this->getCoreVersion()
        );
    }

    /**
     * Get the version of vapor-cli.
     *
     * @return string
     */
    protected function getCliVersion()
    {
        return $this->getApplication()->getVersion();
    }

    /**
     * Get the version of vapor-core.
     *
     * @return string|null
     */
    protected function getCoreVersion()
    {
        if ( ! file_exists($file = Path::current().'/vendor/composer/installed.json')) {
            return;
        }

        $version = collect(json_decode(file_get_contents($file)))
            ->pipe(function ($composer) {
                return collect($composer->get('packages', $composer));
            })
            ->where('name', 'laravel/vapor-core')
            ->first()->version;

        return ltrim($version, 'v');
    }

    /**
     * Setup a signal listener to handle deployment cancellations.
     *
     *
     * @return array
     */
    protected function handleCancellations(array $deployment)
    {
        if ( ! extension_loaded('pcntl')) {
            return $deployment;
        }

        pcntl_async_signals(true);

        pcntl_signal(SIGINT, function () use ($deployment) {
            $this->cancelDeployment($deployment);

            exit;
        });

        return $deployment;
    }

    /**
     * Serve the artifact's assets at the given path.
     *
     *
     * @return void
     */
    protected function serveAssets(array $artifact)
    {
        Helpers::line();

        (new ServeAssets())->__invoke($this->vapor, $artifact);
    }

    /**
     * Upload the deployment artifact.
     *
     * @param string $environment
     * @param string $uuid
     *
     * @return array
     */
    protected function uploadArtifact($environment, $uuid)
    {
        Helpers::line();

        if ( ! Manifest::usesContainerImage($environment)) {
            Helpers::step('<comment>Uploading Deployment Artifact</comment> ('.Helpers::megabytes(Path::artifact()).')');
        }

        $artifact = $this->vapor->createArtifact(
            Manifest::id(),
            $uuid,
            $environment,
            Manifest::usesContainerImage($environment) ? null : Path::artifact(),
            $this->option('commit') ?: Git::hash(),
            $this->option('message') ?: Git::message(),
            Manifest::shouldSeparateVendor($environment) ? $this->createVendorHash() : null,
            $this->getCliVersion(),
            $this->getCoreVersion()
        );

        if (isset($artifact['vendor_url'])) {
            Helpers::line();

            Helpers::step('<comment>Uploading Vendor Directory</comment> ('.Helpers::megabytes(Path::vendorArtifact()).')');

            Helpers::app(AwsStorageProvider::class)->store($artifact['vendor_url'], [], Path::vendorArtifact(), true);
        }

        if (Manifest::usesContainerImage($environment)) {
            Helpers::line();

            Helpers::step('<comment>Pushing Container Image</comment>');

            Docker::publish(
                Path::app(),
                Manifest::name(),
                $environment,
                $artifact['container_registry_token'],
                $artifact['container_repository'],
                $artifact['container_image_tag']
            );
        }

        return $artifact;
    }

    /**
     * Determine if the environment being deployed uses CloudFront.
     *
     * @return bool
     */
    protected function usesCloudFront()
    {
        return Manifest::current()['environments'][$this->argument('environment')]['cloudfront'] ?? true;
    }
}
