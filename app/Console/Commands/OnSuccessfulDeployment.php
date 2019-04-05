<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Exceptions\FileNotFoundException;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class OnSuccessfulDeployment extends Command
{
    // The environment on which builds happen
    const BUILD_ENV              = 'staging';
    const COMMIT_MESSAGE_TRIGGER = 'cpm:publish-build';

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
                                           {comment    : Deployment comment or last commit message for automatic deployments.}
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
        $user    = $this->argument('userName');
        $comment = $this->argument('comment');

        $this->info('previousRevision: '.$lastDeployedRevision);
        $this->info('currentRevision: '.$newlyDeployedRevision);
        $this->info('envName: '.$envName);
        $this->info('rollback: '.$isRollback);
        $this->info('userName: '.$user);
        $this->info('comment: '.$comment);

        $this->publishBuild(
            $envName,
            $isRollback,
            $comment
        );

//        Do this on the build server
//        $this->notifySlackOfJiraTicketsDeployed(
//            $lastDeployedRevision,
//            $newlyDeployedRevision,
//            $envName,
//            $isRollback,
//            $user
//        );
    }

    /**
     * @param string $lastDeployedRevision
     * @param string $newlyDeployedRevision
     * @param string $envName
     * @param bool   $isRollback
     * @param int    $user
     *
     * @throws \Exception
     */
    private function notifySlackOfJiraTicketsDeployed(
        string $lastDeployedRevision,
        string $newlyDeployedRevision,
        string $envName,
        bool $isRollback,
        string $user
    ) {
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

        if (app()->environment(['test', 'staging'])) {
            $channel = '#releases-staging';
        } elseif (app()->environment(['worker', 'production'])) {
            $channel = '#releases-production';
        }

        if ( ! isset($channel)) {
            throw new \Exception(
                'Unable to resolve Slack channel. Check that environment is allowed to run this command.'
            );
        }

        $loginLink = config('opcache.url');
        $message .= "\n Login at: $loginLink";

        sendSlackMessage($channel, $message, true);
    }

    /**
     * Store the build in Cpm Releases repository so that it can be deployed to a production environment.
     *
     * @param string $envName
     * @param bool   $isRollback
     * @param string $comment
     *
     * @throws FileNotFoundException
     */
    private function publishBuild(
        string $envName,
        bool $isRollback,
        string $comment
    ) {
        if (true === $isRollback || self::BUILD_ENV !== strtolower($envName) || ! str_contains(
            $comment,
            self::COMMIT_MESSAGE_TRIGGER
            )) {
            return;
        }

        //the name of the build. Awkwardly named release
        $build = 'release.tar.gz';

        //we'll move the zipped build in this dir to setup git repo and push it to remote repo
        $buildDir = 'releases';

        //this is the actual release (folder we deploy in)
        $releaseRoot = getcwd();

        if ( ! file_exists($build)) {
            throw new FileNotFoundException("`$build` not found in ".getcwd(), 500);
        }

        if ( ! file_exists($buildDir)) {
            mkdir($buildDir);
        }

        chdir($buildDir);

        if ( ! file_exists(base_path('.git'))) {
            $initGit = $this->runCommand(
                'git init'
            );

            $this->runCommand('git remote add origin git@github.com:CircleLinkHealth/cpm-releases.git');
        }

        chdir($releaseRoot);

        $moved = rename($build, getcwd().'/releases/'.$build);

        if ( ! $moved) {
            throw new \Exception("Could not move `$build` into `releases/$build`", 500);
        }

        chdir($buildDir);

        $version = \Version::format('compact');
        $command = "git add $build && git commit -m '$version' && git push -f -u origin master";

        $this->runCommand(
            $command
        );

        chdir($releaseRoot);

        $this->runCommand("rm -rf $buildDir");
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
        $this->info("Running: `$command`");
        $process = new Process($command);
        $process->run();

        if ( ! $process->isSuccessful()) {
            throw new \Exception('Failed to execute process.'.$process->getIncrementalErrorOutput());
        }

        $errors = $process->getErrorOutput();

        if ( ! empty($errors)) {
            $this->info("Errors `{$errors}`");
            \Log::debug('Errors: '.$errors, ['file' => __FILE__, 'line' => __LINE__]);
        }

        $output = $process->getOutput();

        if ($output) {
            $this->info('Output: '.$output);
            \Log::debug('Output: '.$output, ['file' => __FILE__, 'line' => __LINE__]);
        }

        return $process;
    }
}
