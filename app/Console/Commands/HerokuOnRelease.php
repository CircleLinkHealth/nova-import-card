<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HerokuOnRelease extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heroku:onrelease';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this command upon releasing a new version of CPM on Heroku';

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
        if (empty(config('database.connections.mysql.database'))) {
            $this->call('reviewapp:postdeploy');
        }
    
        $this->call('migrate', ['--force', true]);
        $this->call('migrate:views');
        $this->call('deploy:post');
    
        $this->line('heroku:onrelease ran');
    
    }
}
