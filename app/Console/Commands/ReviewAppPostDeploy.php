<?php

namespace App\Console\Commands;

use App\Traits\RunsConsoleCommands;
use Illuminate\Console\Command;

class ReviewAppPostDeploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviewapp:postdeploy';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commands to run on event postdeploy of a Heroku review app.';
    
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
        $branchName = snake_case(getenv('HEROKU_BRANCH'));
        putenv("DB_DATABASE=$branchName");
        $this->call('config:cache', ['-vvv' => true]);
        
        if ($branchName) {
            $this->call('test:prepare-test_suite-db', ['-vvv' => true, 'dbName' => $branchName]);
        }
    }
}
