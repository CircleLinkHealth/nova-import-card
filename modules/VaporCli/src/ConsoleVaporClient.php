<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\VaporCli;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Laravel\VaporCli\Aws\AwsStorageProvider;
use Laravel\VaporCli\Exceptions\NeedsTwoFactorAuthenticationTokenException;

class ConsoleVaporClient
{
    /**
     * Add a team member to the current team.
     *
     * @param string $email
     *
     * @return void
     */
    public function addTeamMember($email, array $permissions)
    {
        $this->requestWithErrorHandling('post', '/api/teams/'.Helpers::config('team').'/members', [
            'email'       => $email,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Get authorized URLs to store the given artifact assets.
     *
     * @param int $artifactId
     *
     * @return array
     */
    public function authorizeArtifactAssets($artifactId, array $files)
    {
        return $this->requestWithErrorHandling('post', '/api/artifacts/'.$artifactId.'/asset-authorizations', [
            'files' => $files,
        ]);
    }

    /**
     * Get all of the load balancers associated with the current team.
     *
     * @return array
     */
    public function balancers()
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/load-balancers');
    }

    /**
     * Get the cache with the given ID.
     *
     * @param string $cacheId
     *
     * @return array
     */
    public function cache($cacheId)
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/caches/'.$cacheId);
    }

    /**
     * Get the metric information for the given cache.
     *
     * @param string $cacheId
     * @param string $period
     *
     * @return array
     */
    public function cacheMetrics($cacheId, $period)
    {
        return $this->requestWithErrorHandling('get', '/api/caches/'.$cacheId.'/metrics?period='.$period);
    }

    /**
     * Get the caches that belong to the current team.
     *
     * @return array
     */
    public function caches()
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/caches');
    }

    /**
     * Attempt to the cancel the given deployment.
     *
     * @param string $deploymentId
     *
     * @return void
     */
    public function cancelDeployment($deploymentId)
    {
        return $this->requestWithErrorHandling(
            'post',
            '/api/deployments/'.$deploymentId.'/cancellation-attempts'
        );
    }

    /**
     * Get all of the certificates for the current team.
     *
     * @param string $domain
     *
     * @return array
     */
    public function certificates($domain = null)
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/certificates?domain='.$domain);
    }

    /**
     * Clone the given environment.
     *
     * @param string $projectId
     * @param string $fromEnvironment
     * @param string $toEnvironment
     *
     * @return array
     */
    public function cloneEnvironment($projectId, $fromEnvironment, $toEnvironment)
    {
        return $this->requestWithErrorHandling('post', '/api/projects/'.$projectId.'/cloned-environments', [
            'from' => $fromEnvironment,
            'name' => $toEnvironment,
        ]);
    }

    /**
     * Get the command with the given ID.
     *
     * @param string $commandId
     *
     * @return array
     */
    public function command($commandId)
    {
        return $this->request('get', '/api/commands/'.$commandId);
    }

    /**
     * Get the command log for the given command.
     *
     * @param string $commandId
     *
     * @return string
     */
    public function commandLog($commandId)
    {
        return $this->request('get', '/api/commands/'.$commandId.'/log')['log'] ?? base64_encode('');
    }

    /**
     * Get all of the commands for the given project and environment.
     *
     * @param string $projectId
     * @param string $environment
     *
     * @return array
     */
    public function commands($projectId, $environment)
    {
        return $this->request('get', '/api/projects/'.$projectId.'/environments/'.$environment.'/commands');
    }

    /**
     * Get a pre-signed storage URL for the given project.
     *
     * @param int    $projectId
     * @param string $uuid
     * @param string $environment
     * @param string $file
     * @param string $commit
     * @param string $commitMessage
     * @param string $vendorHash
     * @param string $cliVersion
     * @param string $coreVersion
     *
     * @return array
     */
    public function createArtifact(
        $projectId,
        $uuid,
        $environment,
        $file = null,
        $commit = null,
        $commitMessage = null,
        $vendorHash = null,
        $cliVersion = null,
        $coreVersion = null
    ) {
        $artifact = $this->requestWithErrorHandling('post', '/api/projects/'.$projectId.'/artifacts/'.$environment, [
            'uuid'                 => $uuid,
            'commit'               => $commit,
            'commit_message'       => $commitMessage,
            'vendor_hash'          => $vendorHash,
            'cli_version'          => $cliVersion,
            'core_version'         => $coreVersion,
            'uses_container_image' => is_null($file),
        ]);

        if ($file) {
            Helpers::app(AwsStorageProvider::class)->store($artifact['url'], [], $file, true);

            try {
                $this->requestWithErrorHandling('post', '/api/artifacts/'.$artifact['id'].'/receipt');
            } catch (ClientException $e) {
                Helpers::abort('Unable to upload deployment artifact to cloud storage.');
            }
        }

        return $artifact;
    }

    /**
     * Create a new load balancer.
     *
     * @param string $networkId
     * @param string $name
     *
     * @return array
     */
    public function createBalancer($networkId, $name)
    {
        return $this->requestWithErrorHandling('post', '/api/networks/'.$networkId.'/load-balancers', [
            'name'                      => $name,
            'https_listener_ssl_policy' => 'ELBSecurityPolicy-TLS-1-2-Ext-2018-06',
        ]);
    }

    /**
     * Create a new cache.
     *
     * @param string $networkId
     * @param string $name
     * @param string $instanceClass
     *
     * @return array
     */
    public function createCache($networkId, $name, $instanceClass)
    {
        return $this->requestWithErrorHandling('post', '/api/networks/'.$networkId.'/caches', [
            'name'           => $name,
            'instance_class' => $instanceClass,
        ]);
    }

    /**
     * Create a new database.
     *
     * @param string $networkId
     * @param string $name
     * @param string $type
     * @param string $instanceClass
     * @param string $storage
     * @param bool   $public
     * @param bool   $pause
     *
     * @return array
     */
    public function createDatabase($networkId, $name, $type, $instanceClass, $storage, $public, $pause = false)
    {
        return $this->requestWithErrorHandling('post', '/api/networks/'.$networkId.'/databases', [
            'name'           => $name,
            'type'           => $type,
            'instance_class' => $instanceClass,
            'storage'        => $storage,
            'public'         => $public,
            'pause'          => $pause,
        ]);
    }

    /**
     * Create a new database proxy.
     *
     * @param string $databaseId
     *
     * @return array
     */
    public function createDatabaseProxy($databaseId)
    {
        return $this->requestWithErrorHandling('post', '/api/databases/'.$databaseId.'/proxy');
    }

    /**
     * Create a new database user.
     *
     * @param string $databaseId
     * @param string $username
     *
     * @return array
     */
    public function createDatabaseUser($databaseId, $username)
    {
        return $this->requestWithErrorHandling('post', '/api/databases/'.$databaseId.'/users', [
            'username' => $username,
        ]);
    }

    /**
     * Create a new environment for the project.
     *
     * @param  int    $projectId
     * @param  string $environment
     * @param  bool   $usesContainerImage
     * @return array
     */
    public function createEnvironment($projectId, $environment, $usesContainerImage = false)
    {
        return $this->requestWithErrorHandling('post', '/api/projects/'.$projectId.'/environments', [
            'name'                 => $environment,
            'uses_container_image' => $usesContainerImage,
        ]);
    }

    /**
     * Create a new jump-box.
     *
     * @param string $networkId
     * @param string $name
     *
     * @return array
     */
    public function createJumpBox($networkId, $name)
    {
        return $this->requestWithErrorHandling('post', '/api/networks/'.$networkId.'/jump-boxes', [
            'name' => $name,
        ]);
    }

    /**
     * Create a new network.
     *
     * @param int    $providerId
     * @param string $name
     * @param string $region
     * @param bool   $withInternetAccess
     *
     * @return array
     */
    public function createNetwork($providerId, $name, $region, $withInternetAccess)
    {
        $this->requestWithErrorHandling('post', '/api/teams/'.Helpers::config('team').'/networks', [
            'cloud_provider_id'    => $providerId,
            'name'                 => $name,
            'region'               => $region,
            'with_internet_access' => $withInternetAccess,
        ]);
    }

    /**
     * Create a new project.
     *
     * @param string $name
     * @param int    $providerId
     * @param string $region
     *
     * @return array
     */
    public function createProject($name, $providerId, $region)
    {
        return $this->requestWithErrorHandling('post', '/api/teams/'.Helpers::config('team').'/projects', array_filter([
            'cloud_provider_id' => $providerId,
            'name'              => $name,
            'region'            => $region,
        ]));
    }

    /**
     * Add a server provider to the authenticated account.
     *
     * @param string $type
     * @param string $name
     *
     * @return void
     */
    public function createProvider($type, $name, array $credentials)
    {
        $this->requestWithErrorHandling('post', '/api/teams/'.Helpers::config('team').'/providers', [
            'type' => $type,
            'name' => $name,
            'meta' => $credentials,
        ]);
    }

    /**
     * Create or update a record.
     *
     * @param string $zoneId
     * @param string $type
     * @param string $name
     * @param string $value
     *
     * @return array
     */
    public function createRecord($zoneId, $type, $name, $value)
    {
        return $this->requestWithErrorHandling('put', '/api/zones/'.$zoneId.'/records', [
            'type'  => $type,
            'name'  => $name,
            'value' => $value,
        ]);
    }

    /**
     * Create a new team.
     *
     * @param string $name
     *
     * @return array
     */
    public function createTeam($name)
    {
        return $this->requestWithErrorHandling('post', '/api/owned-teams', [
            'name' => $name,
        ]);
    }

    /**
     * Create a new zone.
     *
     * @param string $providerId
     * @param string $zone
     *
     * @return array
     */
    public function createZone($providerId, $zone)
    {
        return $this->requestWithErrorHandling('post', '/api/teams/'.Helpers::config('team').'/zones', [
            'cloud_provider_id' => $providerId,
            'zone'              => $zone,
        ]);
    }

    /**
     * Get the database with the given ID.
     *
     * @param string $databaseId
     *
     * @return array
     */
    public function database($databaseId)
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/databases/'.$databaseId);
    }

    /**
     * Get the metric information for the given database.
     *
     * @param string $databaseId
     * @param string $period
     *
     * @return array
     */
    public function databaseMetrics($databaseId, $period)
    {
        return $this->requestWithErrorHandling('get', '/api/databases/'.$databaseId.'/metrics?period='.$period);
    }

    /**
     * Get the databases that belong to the current team.
     *
     * @return array
     */
    public function databases()
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/databases');
    }

    /**
     * Get the password for the given database user.
     *
     * @param string $databaseUserId
     *
     * @return array
     */
    public function databaseUserPassword($databaseUserId)
    {
        return $this->request('get', '/api/database-users/'.$databaseUserId.'/password');
    }

    /**
     * Get the database users for the given database.
     *
     * @param string $databaseId
     *
     * @return array
     */
    public function databaseUsers($databaseId)
    {
        return $this->request('get', '/api/databases/'.$databaseId.'/users');
    }

    /**
     * Get the default team member permissions.
     *
     * @return array
     */
    public function defaultMemberPermissions()
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/default-member-permissions');
    }

    /**
     * Delete the load balancer with the given ID.
     *
     * @param string $balancerId
     *
     * @return void
     */
    public function deleteBalancer($balancerId)
    {
        return $this->requestWithErrorHandling('delete', '/api/load-balancers/'.$balancerId);
    }

    /**
     * Delete the cache with the given ID.
     *
     * @param string $cacheId
     *
     * @return void
     */
    public function deleteCache($cacheId)
    {
        $this->requestWithErrorHandling('delete', '/api/caches/'.$cacheId);
    }

    /**
     * Delete the given certificate.
     *
     * @param string $certificateId
     *
     * @return void
     */
    public function deleteCertificate($certificateId)
    {
        $this->requestWithErrorHandling(
            'delete',
            '/api/certificates/'.$certificateId
        );
    }

    /**
     * Delete the database with the given ID.
     *
     * @param string $databaseId
     *
     * @return void
     */
    public function deleteDatabase($databaseId)
    {
        $this->requestWithErrorHandling('delete', '/api/databases/'.$databaseId);
    }

    /**
     * Delete the proxy associated to the given database.
     *
     * @param string $databaseId
     *
     * @return void
     */
    public function deleteDatabaseProxy($databaseId)
    {
        $this->requestWithErrorHandling('delete', '/api/databases/'.$databaseId.'/proxy');
    }

    /**
     * Delete the given environment.
     *
     * @param string $projectId
     * @param string $environment
     *
     * @return void
     */
    public function deleteEnvironment($projectId, $environment)
    {
        return $this->requestWithErrorHandling(
            'delete',
            'api/projects/'.$projectId.'/environments/'.$environment
        );
    }

    /**
     * Delete the jump box with the given ID.
     *
     * @param string $jumpBoxId
     *
     * @return void
     */
    public function deleteJumpBox($jumpBoxId)
    {
        return $this->requestWithErrorHandling('delete', '/api/jump-boxes/'.$jumpBoxId);
    }

    /**
     * Delete the network with the given ID.
     *
     * @param string $networkId
     *
     * @return array
     */
    public function deleteNetwork($networkId)
    {
        return $this->requestWithErrorHandling('delete', '/api/networks/'.$networkId);
    }

    /**
     * Delete the given project.
     *
     * @param string $projectId
     *
     * @return void
     */
    public function deleteProject($projectId)
    {
        $this->requestWithErrorHandling('delete', '/api/projects/'.$projectId);
    }

    /**
     * Delete the given provider.
     *
     * @param string $providerId
     *
     * @return void
     */
    public function deleteProvider($providerId)
    {
        $this->requestWithErrorHandling('delete', '/api/providers/'.$providerId);
    }

    /**
     * Delete the given record.
     *
     * @param string $zoneId
     * @param string $type
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public function deleteRecord($zoneId, $type, $name, $value = null)
    {
        $this->requestWithErrorHandling('delete', '/api/zones/'.$zoneId.'/records/?type='.$type.'&name='.$name.'&value='.$value);
    }

    /**
     * Delete the given secret.
     *
     * @param string $secretId
     *
     * @return void
     */
    public function deleteSecret($secretId)
    {
        return $this->requestWithErrorHandling(
            'delete',
            'api/secrets/'.$secretId
        );
    }

    /**
     * Delete the given zone.
     *
     * @param string $zoneId
     *
     * @return void
     */
    public function deleteZone($zoneId)
    {
        $this->requestWithErrorHandling('delete', '/api/zones/'.$zoneId);
    }

    /**
     * Deploy the given artifact.
     *
     * @param int $artifactId
     *
     * @return array
     */
    public function deploy($artifactId, array $manifest)
    {
        return $this->requestWithErrorHandling('post', '/api/artifacts/'.$artifactId.'/deployments', [
            'manifest' => $manifest,
        ]);
    }

    /**
     * Get the deployment with the given ID.
     *
     * @param string $deploymentId
     *
     * @return array
     */
    public function deployment($deploymentId)
    {
        return $this->request('get', '/api/deployments/'.$deploymentId);
    }

    /**
     * Get the deployment hook with the given ID.
     *
     * @param string $hookId
     *
     * @return array
     */
    public function deploymentHook($hookId)
    {
        return $this->request('get', '/api/hooks/'.$hookId);
    }

    /**
     * Get the deployment hook output with the given ID.
     *
     * @param string $hookId
     *
     * @return string
     */
    public function deploymentHookOutput($hookId)
    {
        return $this->request('get', '/api/hooks/'.$hookId.'/output');
    }

    /**
     * Get the deployment hooks for the given deployment.
     *
     * @param string $deploymentId
     *
     * @return array
     */
    public function deploymentHooks($deploymentId)
    {
        return $this->request('get', '/api/deployments/'.$deploymentId.'/hooks');
    }

    /**
     * Get all of the deployments for the given project.
     *
     * @param string $projectId
     * @param string $environment
     *
     * @return array
     */
    public function deployments($projectId, $environment)
    {
        return $this->request('get', '/api/projects/'.$projectId.'/environments/'.$environment.'/deployments');
    }

    /**
     * Disable maintenance mode for the given environment.
     *
     * @param string $projectId
     * @param string $environment
     *
     * @return array
     */
    public function disableMaintenanceMode($projectId, $environment)
    {
        return $this->requestWithErrorHandling(
            'delete',
            '/api/projects/'.$projectId.'/environments/'.$environment.'/maintenance-mode-deployments'
        );
    }

    /**
     * Drop the given database user.
     *
     * @param string $databaseUserId
     *
     * @return void
     */
    public function dropDatabaseUser($databaseUserId)
    {
        $this->requestWithErrorHandling('delete', '/api/database-users/'.$databaseUserId);
    }

    /**
     * Enable maintenance mode for the given environment.
     *
     * @param string $projectId
     * @param string $environment
     * @param string $secret
     *
     * @return array
     */
    public function enableMaintenanceMode($projectId, $environment, $secret = null)
    {
        return $this->requestWithErrorHandling(
            'post',
            '/api/projects/'.$projectId.'/environments/'.$environment.'/maintenance-mode-deployments',
            [
                'secret' => $secret,
            ]
        );
    }

    /**
     * Get the environment by a specific name for the given project.
     *
     * @param string $projectId
     * @param mixed  $name
     *
     * @return array
     */
    public function environmentNamed($projectId, $name)
    {
        return collect($this->environments($projectId))->first(function ($environment) use ($name) {
            return $environment['name'] === $name;
        });
    }

    /**
     * Get all of the environments for the given project.
     *
     * @param string $projectId
     *
     * @return array
     */
    public function environments($projectId)
    {
        return $this->request('get', '/api/projects/'.$projectId.'/environments');
    }

    /**
     * Get the environment variables for the given environment.
     *
     * @param int    $projectId
     * @param string $environment
     *
     * @return string
     */
    public function environmentVariables($projectId, $environment)
    {
        return $this->request('get', 'api/projects/'.$projectId.'/environments/'.$environment.'/variables')['variables'];
    }

    /**
     * Grant the given network Internet access via a NAT Gateway.
     *
     * @param int $networkId
     *
     * @return void
     */
    public function grantNetworkInternetAccess($networkId)
    {
        $this->requestWithErrorHandling('post', '/api/networks/'.$networkId.'/nat');
    }

    /**
     * Execute a command in a given environment.
     *
     * @param string $projectId
     * @param string $environment
     * @param string $command
     *
     * @return array
     */
    public function invoke($projectId, $environment, $command)
    {
        return $this->requestWithErrorHandling('post', '/api/projects/'.$projectId.'/environments/'.$environment.'/commands', [
            'command' => $command,
        ]);
    }

    /**
     * Get all of the jump-boxes associated with the current team.
     *
     * @return array
     */
    public function jumpBoxes()
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/jump-boxes');
    }

    /**
     * Get the public / private key for the given jump-box.
     *
     * @param string $jumpBoxId
     *
     * @return array
     */
    public function jumpBoxKey($jumpBoxId)
    {
        return $this->request('get', '/api/jump-boxes/'.$jumpBoxId.'/key');
    }

    /**
     * Get the command that was last executed by the user.
     *
     * @return array
     */
    public function latestCommand()
    {
        return $this->request('get', '/api/latest-command')['command'];
    }

    /**
     * Get the deployment hook that was last executed by the user.
     *
     * @return array
     */
    public function latestFailedDeploymentHook()
    {
        return $this->request('get', '/api/latest-failed-hook')['hook'];
    }

    /**
     * Attempt to obtain an API token from Vapor using a email / password.
     *
     * @param string      $email
     * @param string      $password
     * @param string|null $twoFactorAuthenticationToken
     *
     * @return string
     */
    public function login($email, $password, $twoFactorAuthenticationToken = null)
    {
        try {
            return $this->request('post', '/api/login', [
                'host'             => gethostname(),
                'email'            => $email,
                'password'         => $password,
                'two_factor_token' => $twoFactorAuthenticationToken,
            ])['access_token'];
        } catch (ClientException $e) {
            if (422 === $e->getResponse()->getStatusCode()) {
                $response = json_decode((string) $e->getResponse()->getBody(), true);

                if (isset($response['errors']['two_factor_token'])) {
                    throw new NeedsTwoFactorAuthenticationTokenException();
                }
            }

            throw $e;
        }
    }

    /**
     * Get the metric information for the given environment.
     *
     * @param string $projectId
     * @param string $environment
     * @param string $period
     *
     * @return array
     */
    public function metrics($projectId, $environment, $period)
    {
        return $this->requestWithErrorHandling('get', '/api/projects/'.$projectId.'/environments/'.$environment.'/metrics?period='.$period);
    }

    /**
     * Get the network with the given ID.
     *
     * @param string $networkId
     *
     * @return array
     */
    public function network($networkId)
    {
        return $this->request('get', '/api/networks/'.$networkId);
    }

    /**
     * Get the networks that belong to the account.
     *
     * @return array
     */
    public function networks()
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/networks');
    }

    /**
     * Get the teams that the user owns.
     *
     * @return array
     */
    public function ownedTeams()
    {
        return $this->request('get', '/api/owned-teams');
    }

    /**
     * Get all of the pending certificates for the team.
     *
     * @return array
     */
    public function pendingCertificates()
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/pending-certificates');
    }

    /**
     * Get the project with the given ID.
     *
     * @param string $projectId
     *
     * @return array
     */
    public function project($projectId)
    {
        return $this->request('get', '/api/projects/'.$projectId);
    }

    /**
     * Get all of the providers attached to the account.
     *
     * @return array
     */
    public function providers()
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/providers');
    }

    /**
     * Store meta information for the given artifact assets.
     *
     * @param int $artifactId
     *
     * @return array
     */
    public function recordArtifactAssets($artifactId, array $files)
    {
        return $this->requestWithErrorHandling('post', '/api/artifacts/'.$artifactId.'/assets', [
            'files' => $files,
        ]);
    }

    /**
     * Get the DNS records for the given zone.
     *
     * @param string $zone
     * @param mixed  $zoneId
     *
     * @return array
     */
    public function records($zoneId)
    {
        return $this->request('get', '/api/zones/'.$zoneId.'/records');
    }

    /**
     * Redeploy the given environment's latest deployment.
     *
     * @param string $projectId
     * @param string $environment
     *
     * @return array
     */
    public function redeploy($projectId, $environment)
    {
        return $this->requestWithErrorHandling(
            'post',
            '/api/projects/'.$projectId.'/environments/'.$environment.'/redeployments'
        );
    }

    /**
     * Remove the given network's Internet access.
     *
     * @param int $networkId
     *
     * @return void
     */
    public function removeNetworkInternetAccess($networkId)
    {
        $this->requestWithErrorHandling('delete', '/api/networks/'.$networkId.'/nat');
    }

    /**
     * Remove a team member from the current team.
     *
     * @param string $email
     *
     * @return void
     */
    public function removeTeamMember($email)
    {
        $this->requestWithErrorHandling('delete', '/api/teams/'.Helpers::config('team').'/members', [
            'email' => $email,
        ]);
    }

    /**
     * Request a certificate for the given domain.
     *
     * @param string $providerId
     * @param string $domain
     * @param string $validationMethod
     * @param mixed  $region
     *
     * @return array
     */
    public function requestCertificate($providerId, $domain, array $alternativeNames, $region, $validationMethod)
    {
        $this->requestWithErrorHandling('post', '/api/teams/'.Helpers::config('team').'/certificates', [
            'cloud_provider_id' => $providerId,
            'domain'            => $domain,
            'alternative_names' => $alternativeNames,
            'region'            => $region,
            'validation_method' => $validationMethod,
        ]);
    }

    /**
     * Resend the validation email for the given certificate.
     *
     * @param string $certificateId
     *
     * @return void
     */
    public function resendCertificateValidationEmail($certificateId)
    {
        $this->requestWithErrorHandling(
            'post',
            '/api/certificates/'.$certificateId.'/validation-email'
        );
    }

    /**
     * Restore the given database to new database at a given point in time.
     *
     * @param string $databaseId
     * @param string $name
     * @param int    $restoreTo
     *
     * @return array
     */
    public function restoreDatabase($databaseId, $name, $restoreTo)
    {
        return $this->requestWithErrorHandling('post', '/api/restored-databases?database_id='.$databaseId, [
            'name'       => $name,
            'restore_to' => $restoreTo,
        ]);
    }

    /**
     * Rollback to the given deployment ID.
     *
     * @param string $deploymentId
     *
     * @return array
     */
    public function rollbackTo($deploymentId)
    {
        return $this->requestWithErrorHandling('post', '/api/rollbacks', [
            'deployment' => $deploymentId,
        ]);
    }

    /**
     * Rotate the given database's password.
     *
     * @param string $databaseId
     *
     * @return string
     */
    public function rotateDatabasePassword($databaseId)
    {
        return $this->requestWithErrorHandling(
            'post',
            '/api/databases/'.$databaseId.'/password-rotations'
        )['password'];
    }

    /**
     * Scale the given cache.
     *
     * @param string $cacheId
     * @param int    $scale
     *
     * @return void
     */
    public function scaleCache($cacheId, $scale)
    {
        $this->requestWithErrorHandling('put', '/api/caches/'.$cacheId.'/size', [
            'scale' => $scale,
        ]);
    }

    /**
     * Scale the given database.
     *
     * @param string $databaseId
     * @param string $instanceClass
     * @param int    $storage
     *
     * @return void
     */
    public function scaleDatabase($databaseId, $instanceClass, $storage)
    {
        $this->requestWithErrorHandling('put', '/api/databases/'.$databaseId.'/size', [
            'instance_class' => $instanceClass,
            'storage'        => $storage,
        ]);
    }

    /**
     * Get all of the secrets for the given environment.
     *
     * @param string $projectId
     * @param string $environment
     *
     * @return array
     */
    public function secrets($projectId, $environment)
    {
        return $this->request(
            'get',
            'api/projects/'.$projectId.'/environments/'.$environment.'/secrets'
        );
    }

    /**
     * Store a secret for the given environment.
     *
     * @param string $projectId
     * @param string $environment
     * @param string $name
     * @param string $value
     *
     * @return array
     */
    public function storeSecret($projectId, $environment, $name, $value)
    {
        return $this->requestWithErrorHandling(
            'put',
            'api/projects/'.$projectId.'/environments/'.$environment.'/secrets',
            ['name' => $name, 'value' => $value]
        );
    }

    /**
     * Switch the current team the user is interacting with.
     *
     * @param string $teamId
     *
     * @return void
     */
    public function switchCurrentTeam($teamId)
    {
        $this->requestWithErrorHandling('put', '/api/current-team', [
            'team_id' => $teamId,
        ]);
    }

    /**
     * Get the latest log information for the given environment.
     *
     * @param string $projectId
     * @param string $environment
     * @param bool   $cli
     * @param string $filter
     * @param int    $start
     * @param string $nextToken
     *
     * @return array
     */
    public function tail($projectId, $environment, $cli, $filter, $start, $nextToken = null)
    {
        $url = sprintf(
            '/api/projects/%s/environments/%s/tailed-log?cli=%s&filter=%s&start=%s&next_token=%s',
            $projectId,
            $environment,
            true === $cli ? 'true' : 'false',
            $filter,
            $start,
            $nextToken
        );

        return $this->request('get', $url);
    }

    /**
     * Get the team members of the current team.
     *
     * @return array
     */
    public function teamMembers()
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/members');
    }

    /**
     * Get the teams that the user belongs to.
     *
     * @return array
     */
    public function teams()
    {
        return $this->request('get', '/api/teams');
    }

    /**
     * Update the environment variables for the given environment.
     *
     * @param int    $projectId
     * @param string $environment
     * @param string $variables
     *
     * @return string
     */
    public function updateEnvironmentVariables($projectId, $environment, $variables)
    {
        return $this->requestWithErrorHandling(
            'put',
            'api/projects/'.$projectId.'/environments/'.$environment.'/variables',
            ['variables' => $variables]
        );
    }

    /**
     * Update a server provider.
     *
     * @param string $providerId
     * @param string $name
     *
     * @return array
     */
    public function updateProvider($providerId, $name, array $credentials)
    {
        return $this->requestWithErrorHandling('put', '/api/providers/'.$providerId, [
            'name' => $name,
            'meta' => $credentials,
        ]);
    }

    /**
     * Get the currently authenticated user.
     *
     * @return array
     */
    public function user()
    {
        return $this->request('get', '/api/user');
    }

    /**
     * Validate the given manifest for the project.
     *
     * @param string $projectId
     * @param string $environment
     * @param string $cliVersion
     * @param string $coreVersion
     *
     * @return void
     */
    public function validateManifest($projectId, $environment, array $manifest, $cliVersion = null, $coreVersion = null)
    {
        $this->requestWithErrorHandling('post', '/api/projects/'.$projectId.'/environments/'.$environment.'/linted-manifest', [
            'manifest'     => $manifest,
            'cli_version'  => $cliVersion,
            'core_version' => $coreVersion,
        ]);
    }

    /**
     * Get the zone with the given ID.
     *
     * @param string $zoneId
     *
     * @return array
     */
    public function zone($zoneId)
    {
        return $this->request('get', '/api/zones/'.$zoneId);
    }

    /**
     * Get the zones that belong to the account.
     *
     * @return array
     */
    public function zones()
    {
        return $this->request('get', '/api/teams/'.Helpers::config('team').'/zones');
    }

    /**
     * Get a HTTP client instance.
     *
     * @return Client
     */
    protected function client()
    {
        return new Client([
            'base_uri' => $_ENV['VAPOR_API_BASE'] ?? getenv('VAPOR_API_BASE') ?: 'https://vapor.laravel.com',
            // 'base_uri' => $_ENV['VAPOR_API_BASE'] ?? 'https://laravel-vapor.ngrok.io',
        ]);
    }

    /**
     * Display the errors for the request.
     *
     * @param Response $response
     *
     * @return void
     */
    protected function displayRequestErrors($response)
    {
        if (401 === $response->getStatusCode()) {
            Helpers::abort('Please authenticate with Vapor using the "login" command.');
        }

        if (402 === $response->getStatusCode()) {
            Helpers::abort('An active subscription is required to perform this action.');
        }

        if (403 === $response->getStatusCode()) {
            Helpers::abort('You are not authorized to perform this action.');
        }

        if (404 === $response->getStatusCode()) {
            Helpers::abort('The requested resource does not exist.');
        }

        if (409 === $response->getStatusCode()) {
            Helpers::abort('This operation is already in progress. Please try again later.');
        }

        if (429 === $response->getStatusCode()) {
            Helpers::abort('You are attempting this action too often.');
        }
    }

    /**
     * Display the validation errors for the given response.
     *
     * @param Response $response
     *
     * @return void
     */
    protected function displayValidationErrors($response)
    {
        $errors = collect(json_decode(
            (string) $response->getBody(),
            true
        )['errors'])->flatten();

        Helpers::line('');
        Helpers::danger('Whoops! There were some problems with your request.');
        Helpers::line('');

        foreach ($errors as $error) {
            Helpers::line("    - {$error}");
        }

        Helpers::line('');
    }

    /**
     * Make a request to the API and return the resulting JSON array.
     *
     * @param string $method
     * @param string $uri
     * @param int    $tries
     *
     * @return array
     */
    protected function request($method, $uri, array $json = [], $tries = 0)
    {
        try {
            return $this->requestWithoutErrorHandling($method, $uri, $json);
        } catch (ClientException $e) {
            $response = $e->getResponse();

            if (429 === $response->getStatusCode() && $response->hasHeader('retry-after') && $tries < 3) {
                $retryAfter = $response->getHeader('retry-after')[0];

                Helpers::line("You are attempting this action too often. Retrying in [{$retryAfter}] seconds...");

                sleep($retryAfter + 1);

                return $this->request($method, $uri, $json, $tries + 1);
            }

            $this->displayRequestErrors($response);

            throw $e;
        }
    }

    /**
     * Make an HTTP request and display any validation errors.
     *
     * @param string $method
     * @param string $uri
     *
     * @return array
     */
    protected function requestWithErrorHandling($method, $uri, array $json = [])
    {
        try {
            return $this->request($method, $uri, $json);
        } catch (ClientException $e) {
            $response = $e->getResponse();

            if (in_array($response->getStatusCode(), [400, 422])) {
                $this->displayValidationErrors($response);

                exit(1);
            }

            throw $e;
        }
    }

    /**
     * Make a request to the API and return the resulting JSON array.
     *
     * @param string $method
     * @param string $uri
     *
     * @return array
     */
    protected function requestWithoutErrorHandling($method, $uri, array $json = [])
    {
        return json_decode((string) $this->client()->request($method, ltrim($uri, '/'), [
            'json'    => $json,
            'headers' => [
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer '.Helpers::config('token', $_ENV['VAPOR_API_TOKEN'] ?? getenv('VAPOR_API_TOKEN') ?? null),
            ],
        ])->getBody(), true);
    }
}
