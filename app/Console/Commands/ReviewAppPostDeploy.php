<?php

namespace App\Console\Commands;

use App\Traits\RunsConsoleCommands;
use Illuminate\Console\Command;

class ReviewAppPostDeploy extends Command
{
    use RunsConsoleCommands;
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
    protected $description = 'Commands to run on event postdeploy of a Heroku review app. Only run this for review apps.';
    
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
        if ( ! app()->environment(['review', 'local'])) {
            throw new \Exception('Only review and local environments can run this');
        }
        
        $branchName = snake_case(getenv('HEROKU_BRANCH'));
        
        putenv("DB_DATABASE=$branchName");
        
        config(['database.mysql.database' => $branchName]);
        
        $this->runCommand(['php', 'artisan', 'config:cache', '-vvv']);
        
        if ($branchName) {
            $migrateInstallCommand  = $this->runCommand(['php', 'artisan', '-vvv', 'mysql:createdb', $branchName]);
            $migrateCommand         = $this->runCommand(['php', 'artisan', '-vvv', 'migrate:fresh']);
            $migrateCommand         = $this->runCommand(['php', 'artisan', '-vvv', 'migrate:views']);
            $testSuiteSeederCommand = $this->runCommand(['php', 'artisan', '-vvv', 'db:seed', '--class=TestSuiteSeeder']);
        }
        
        $this->warn('reviewapp:postdeploy ran');
    }
}
