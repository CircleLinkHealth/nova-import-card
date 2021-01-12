<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OnSuccessfulDeployment extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this command after a successful deployment po perform related tasks (Version Updates)';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:success {currentRevision : The revision that was just successfully deployed.}
                                           {envName : The name of the environment we just deployed to.}
                                           {rollback    : Either 1 or 0 if deployment is a rollback or not.}
                                           {userName    : Name of the user who triggered the deployment.}
                                           {previousRevision?    : The revision deployed before the one just deployed.}
    ';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function handle()
    {
        $lastDeployedRevision  = $this->argument('previousRevision');
        $newlyDeployedRevision = $this->argument('currentRevision');
        $envName               = $this->argument('envName');
        $isRollback            = 1 == $this->argument('rollback')
            ? true
            : false;
        $user = $this->argument('userName');

        \Artisan::call(StoreJiraTicketsDeployed::class, [
            'currentRevision'  => $newlyDeployedRevision,
            'envName'          => $envName,
            'rollback'         => $isRollback,
            'userName'         => $user,
            'previousRevision' => $lastDeployedRevision,
        ]);

        $this->info('previousRevision: '.$lastDeployedRevision);
        $this->info('currentRevision: '.$newlyDeployedRevision);
        $this->info('envName: '.$envName);
        $this->info('rollback: '.$isRollback);
        $this->info('userName: '.$user);

        \Artisan::call(NotifyRaygunOfDeployment::class, ['scmIdentifier' => $newlyDeployedRevision]);
        $this->notifySlackOfTicketsDeployed();
    }

    /**
     * @throws \Exception
     */
    private function notifySlackOfTicketsDeployed()
    {
        $filePath = storage_path('jira-tickets-deployed');

        if (file_exists($filePath)) {
            $contents = json_decode(file_get_contents($filePath), true);

            if (array_key_exists('message', $contents)) {
                $message = $contents['message'];
                if ( ! isProductionEnv()) {
                    $channel = '#releases-staging';
                } else {
                    $channel = '#releases-production';
                }

                if ( ! isset($channel)) {
                    throw new \Exception('Unable to resolve Slack channel. Check that environment is allowed to run this command.');
                }

                sendSlackMessage($channel, $message, true);
            }
        }
    }
}
