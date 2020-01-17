<?php

namespace App\Console\Commands;

use App\Traits\RunsConsoleCommands;
use Illuminate\Console\Command;

class HerokuOnRelease extends Command
{
    use RunsConsoleCommands;
    
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
        $this->warn('heroku:onrelease ran');
    
        $base = ['php', 'artisan', '-vvv'];
        
        if (app()->environment('review', 'testing')) {
            $this->runCommand(array_merge($base, ['reviewapp:postdeploy']));
        }
        
        $this->runCommand(array_merge($base, ['migrate', '--force']));
        $this->runCommand(array_merge($base, ['migrate:views']));
        $this->runCommand(array_merge($base, ['deploy:post']));
        
        
        $this->warn('heroku:onrelease ran');
    }
}
