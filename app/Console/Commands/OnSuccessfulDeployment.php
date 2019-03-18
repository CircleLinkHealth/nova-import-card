<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class OnSuccessfulDeployment extends Command
{
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
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this command after a successful deployment po perform related tasks (Version Updates)';
    
    /**
     * Create a new command instance.
     *
     * @return void
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
        $envName = $this->argument('envName');
        $isRollback = $this->argument('rollback');
        $user = $this->argument('userName');
        
        $command = "git log --pretty=oneline $lastDeployedRevision...$newlyDeployedRevision | perl -ne '{ /(CPM)-(\d+)/ && print \"$1-$2\n\" }' | sort | uniq";
        $process = new Process($command);
        $process->run();
        
        if (! $process->isSuccessful()) {
            throw new \Exception('Failed to execute process.' . $process->getIncrementalErrorOutput());
        }
    
        $output = $process->getOutput();
        
        $message = "*$user* deployed the following tickets to *$envName*: \n";
        $jiraTickets = collect(explode("\n", $output))
            ->sort()
            ->each(function ($t) use (&$message) {
                if (!empty($t))
                $message.="https://circlelinkhealth.atlassian.net/browse/$t  \n";
            });
        
        sendSlackMessage('#deployments', $message, true);
    }
}
