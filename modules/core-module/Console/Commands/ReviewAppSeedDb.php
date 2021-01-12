<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Console\Commands;

use CircleLinkHealth\Core\Traits\RunsConsoleCommands;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class ReviewAppSeedDb extends Command
{
    use RunsConsoleCommands;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this command upon releasing a new version of CPM on Heroku';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviewapp:seed-db';

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
     * @return void
     */
    public function handle()
    {
        if ( ! app()->environment(['review', 'local', 'testing'])) {
            return;
        }

        $this->output->note('Running reviewapp:seed-db');

        $dbName = config('database.connections.mysql.database');

        $this->output->note("Checking if should run seeder on db [$dbName]");

        try {
            $dbTableExists = User::where('username', 'administrator')->exists()
                && User::where('username', 'care-center')->exists();
        } catch (\Exception $exception) {
            $dbTableExists = false;
        }

        if (false === $dbTableExists) {
            $cmd = 'db:seed';
            $this->output->note("Running command $cmd");
            $this->runCpmCommand(['php', 'artisan', '-vvv', $cmd, '--class=TestSuiteSeeder']);
        }

        $this->warn('reviewapp:seed-db ran');
    }
}
