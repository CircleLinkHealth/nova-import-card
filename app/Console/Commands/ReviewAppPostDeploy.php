<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Traits\RunsConsoleCommands;
use Illuminate\Console\Command;

class ReviewAppPostDeploy extends Command
{
    use RunsConsoleCommands;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commands to run on event postdeploy of a Heroku review app. Only run this for review apps.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviewapp:postdeploy';

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
        if ( ! app()->environment(['review', 'local', 'testing'])) {
            throw new \Exception('Only review and local environments can run this');
        }

        $dbName = config('database.connections.mysql.database');

        try {
            $dbTableExists = \Schema::hasTable('practices');
        } catch (\Exception $exception) {
            $dbTableExists = false;
        }

        if (false === $dbTableExists) {
            $migrateInstallCommand  = $this->runCommand(['php', 'artisan', '-vvv', 'mysql:createdb', $dbName]);
            $migrateCommand         = $this->runCommand(['php', 'artisan', '-vvv', 'migrate:fresh']);
            $migrateCommand         = $this->runCommand(['php', 'artisan', '-vvv', 'migrate:views']);
            $testSuiteSeederCommand = $this->runCommand(['php', 'artisan', '-vvv', 'db:seed', '--class=TestSuiteSeeder']);
        }

        $this->warn('reviewapp:postdeploy ran');
    }
}
