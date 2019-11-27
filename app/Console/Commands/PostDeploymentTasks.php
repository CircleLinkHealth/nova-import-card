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
        if (app()->environment(['local', 'testing'])) {
            echo 'Not running because env is '.app()->environment().PHP_EOL;

            return;
        }

        collect(
            [
                'nova:publish --force',
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
                $this->output->note("Running ${command}");

                \Artisan::call($command);

                $this->output->success("Finished running ${command}");
            }
        );
    }
}
