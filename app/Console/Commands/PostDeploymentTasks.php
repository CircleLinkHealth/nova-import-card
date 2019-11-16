<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PostDeploymentTasks extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run post deployment tasks';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:post';

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
        collect(
            [
                'nova:publish',
                'view:clear',
                'route:cache',
                'config:cache',
                'opcache:clear',
                'opcache:optimize',
                'horizon:terminate',
                'queue:restart',
            ]
        )->each(
            function ($command) {
                if ( ! isQueueWorkerEnv() && in_array(
                    $command,
                    [
                        'horizon:terminate',
                        'queue:restart',
                    ]
                )) {
                    //@todo: start using envoyer scripts
                    //Do not run Queue commands on production, as Worker now takes care of Prod jobs
                    return;
                }

                $this->output->note("Running ${command}");

                \Artisan::call($command);

                $this->output->success("Finished running ${command}");
            }
        );
    }
}
