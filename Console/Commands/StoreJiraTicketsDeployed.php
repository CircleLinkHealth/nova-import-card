<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Console\Commands;

use CircleLinkHealth\Core\Traits\RunsCommands;
use Illuminate\Console\Command;

class StoreJiraTicketsDeployed extends Command
{
    use RunsCommands;

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
    protected $signature = 'tickets:store
                                           {currentRevision : The revision that was just successfully deployed.}
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

        $this->info('previousRevision: '.$lastDeployedRevision);
        $this->info('currentRevision: '.$newlyDeployedRevision);
        $this->info('envName: '.$envName);
        $this->info('rollback: '.$isRollback);
        $this->info('userName: '.$user);

        if ( ! file_exists(base_path('.git'))) {
            $initGit = $this->runCommand(
                'git init && git remote add origin git@github.com:CircleLinkHealth/app-cpm-web.git && git fetch'
            );
        }
        $jiraTicketNumbers = $this->runCommand(
            "git log --pretty=oneline $lastDeployedRevision...$newlyDeployedRevision | perl -ne '{ /(CPM)-(\d+)/ && print \"$1-$2\n\" }' | sort | uniq"
        );

        $output = $jiraTicketNumbers->getOutput();
        $this->info("Output `$output`");

        $jiraTickets = collect(explode("\n", $output))
            ->filter()
            ->values()
            ->sort();

        if ($jiraTickets->isEmpty()) {
            $this->info('No Jira tickets found');

            return;
        }

        $message = "*$user* deployed work related to the following tickets *$envName*: \n";

        $jiraTickets->each(
            function ($t) use (&$message) {
                if ( ! empty($t)) {
                    $message .= "https://circlelinkhealth.atlassian.net/browse/$t  \n";
                }
            }
        );

        $loginLink = config('app.url');
        $message .= "\n Login at: $loginLink";

        $stored = file_put_contents(storage_path('jira-tickets-deployed'), json_encode(['message' => $message]));

        if (false === $stored) {
            throw new \Exception('Could not store file');
        }

        $this->line('Jira tickets file successfully created');
    }
}
