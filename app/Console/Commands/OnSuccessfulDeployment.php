<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

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
    protected $signature = 'deploy:success {previousRevision    : The revision deployed before the one just deployed.}
                                           {currentRevision : The revision that was just successfully deployed.}
                                           {envName : The name of the environment we just deployed to.}
                                           {rollback    : Either 1 or 0 if deployment is a rollback or not.}
                                           {userName    : Name of the user who triggered the deployment.}
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
     * @return mixed
     */
    public function handle()
    {
        $lastDeployedRevision  = $this->argument('previousRevision');
        $newlyDeployedRevision = $this->argument('currentRevision');
        $envName               = $this->argument('envName');
        $isRollback            = $this->argument('rollback');
        $user                  = $this->argument('userName');

        $command = "git log --pretty=oneline $lastDeployedRevision...$newlyDeployedRevision | perl -ne '{ /(CPM)-(\d+)/ && print \"$1-$2\n\" }' | sort | uniq";
        $this->info("Running `$command`");
        $process = new Process($command);
        $outcome = $process->run();

        $this->info("Outcome `$outcome`");

        if ( ! $process->isSuccessful()) {
            throw new \Exception('Failed to execute process.'.$process->getIncrementalErrorOutput());
        }

        $output = $process->getOutput();

        $this->info("Output `$output`");
        $this->info("Errors `{$process->getErrorOutput()}`");

        \Log::debug('Output: '.$output);
        \Log::debug('Error: '.$process->getErrorOutput());

        $message     = "*$user* deployed the following tickets to *$envName*: \n";
        $jiraTickets = collect(explode("\n", $output))
            ->sort()
            ->each(function ($t) use (&$message) {
                if ( ! empty($t)) {
                    $message .= "https://circlelinkhealth.atlassian.net/browse/$t  \n";
                }
            });

        sendSlackMessage('#deployments', $message, true);
    }
}
