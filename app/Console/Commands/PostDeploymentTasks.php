<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PostDeploymentTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run post deployment tasks';

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
        collect([
            'view:clear',
            'route:cache',
            'config:cache',
            'opcache:clear',
            'opcache:optimize',
            'horizon:terminate',
            'queue:restart',
        ])->each(function ($command) {
            $this->output->note("Running $command");

            \Artisan::call($command);

            $this->output->success("Finished running $command");
        });
    }
}
