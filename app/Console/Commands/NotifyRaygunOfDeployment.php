<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class NotifyRaygunOfDeployment extends Command
{
    /**
     * Raygun Deployments API endpoint.
     */
    const RAYGUN_DEPLOYMENTS_ENDPOINT = 'https://app.raygun.io/deployments';
    /**
     * The source control system we use.
     */
    const RAYGUN_SCM_TYPE = 'Github';
    /**
     * @var Client
     */
    protected $client;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify Raygun of deployment.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'raygun:notify
                                    {scmIdentifier : The commit that this deployment was built off.}
                                    ';

    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ( ! config('cpm-module-raygun.enable_crash_reporting')) {
            $this->warn('"cpm-module-raygun.enable_crash_reporting" is disabled. Doing nothing.');

            return;
        }

        $response = $this->client->post(self::RAYGUN_DEPLOYMENTS_ENDPOINT, [
            'apiKey'        => config('cpm-module-raygun.api_key'),
            'version'       => config('app.app_version'),
            'comment'       => 'Deployed at '.Carbon::now()->toDateTimeString(),
            'scmIdentifier' => $this->argument('scmIdentifier') ?? null,
            'scmType'       => self::RAYGUN_SCM_TYPE,
        ]);

        $this->comment($response->getStatusCode().':'.$response->getReasonPhrase());
    }
}
