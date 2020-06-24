<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

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
        echo \Config::get('opcache.url');
        $arr = [
            'view:clear',
            'route:clear',
            'config:clear',
        ];
        if ( ! app()->environment(['review'])) {
            $arr[] = 'opcache:clear';
            $arr[] = 'opcache:compile';
        }
        $arr = array_merge($arr, [
            'config:cache',
            'view:cache',
            'route:cache',
            'horizon:terminate',
            'queue:restart',
        ]);

        collect(
            $arr
        )->each(
            function ($command) {
                $this->output->note("Running ${command}");

                Artisan::call($command, [
                    '-vvv' => true,
                ]);

                $this->output->success("Finished running ${command}");
            }
        );
    }
}
