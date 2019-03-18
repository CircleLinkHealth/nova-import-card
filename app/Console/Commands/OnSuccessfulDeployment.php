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

        if ( ! file_exists(base_path('.git'))) {
            $initGit = $this->runCommand(
                "git init && git remote add origin git@github.com:CircleLinkHealth/app-cpm-web.git && git fetch $lastDeployedRevision && git fetch $newlyDeployedRevision"
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

        // Uncomment for testing
//        if (app()->environment(['local'])) {
//            $channel = '#dev-chat';
//        }

        if (app()->environment(['test', 'staging'])) {
            $channel = '#releases-staging';
        } elseif (app()->environment(['worker', 'production'])) {
            $channel = '#releases-production';
        }

        if ( ! isset($channel)) {
            throw new \Exception('Unable to resolve Slack channel. Check that environment is allowed to run this command.');
        }

        $loginLink = config('opcache.url');
        $message .= "\n Login at: $loginLink";

        sendSlackMessage($channel, $message, true);
    }

    /**
     * @param string $command
     *
     * @throws \Exception
     *
     * @return Process
     */
    private function runCommand(string $command)
    {
        $this->info("Running `$command`");
        $process = new Process($command);
        $process->run();

        if ( ! $process->isSuccessful()) {
            throw new \Exception('Failed to execute process.'.$process->getIncrementalErrorOutput());
        }

        $errors = $process->getErrorOutput();

        $this->info("Errors `{$errors}`");

        if ( ! empty($errors)) {
            \Log::debug('Errors: '.$errors, ['file' => __FILE__, 'line' => __LINE__]);
        }

        $output = $process->getOutput();

        $this->info('Output: '.$output);

        if ($output) {
            \Log::debug('Output: '.$output, ['file' => __FILE__, 'line' => __LINE__]);
        }

        return $process;
    }
}
