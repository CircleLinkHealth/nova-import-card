<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Traits\RunsConsoleCommands;
use CircleLinkHealth\Customer\Entities\User;
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
     * @throws \Exception
     */
    public function handle()
    {
        $this->output->note('Running post deploy command');

        if ( ! app()->environment(['review', 'local', 'testing'])) {
            throw new \Exception('Only review and local environments can run this');
        }

        $dbName = config('database.connections.mysql.database');

        $this->output->note("Checking if db [$dbName] exists");

        try {
            $dbTableExists = User::where('username', 'admin')->exists() && User::where('username', 'nurse')->exists();
        } catch (\Exception $exception) {
            $dbTableExists = false;
        }

        if (false === $dbTableExists) {
            $cmd = 'mysql:createdb';
            $this->output->note("Running command $cmd");
            $this->runCommand(['php', 'artisan', '-vvv', $cmd, $dbName]);

            $cmd = 'migrate:fresh';
            $this->output->note("Running command $cmd");
            $this->runCommand(['php', 'artisan', '-vvv', $cmd]);

            $cmd = 'migrate:views';
            $this->output->note("Running command $cmd");
            $this->runCommand(['php', 'artisan', '-vvv', $cmd]);

            $cmd = 'db:seed';
            $this->output->note("Running command $cmd");
            $this->runCommand(['php', 'artisan', '-vvv', $cmd, '--class=TestSuiteSeeder']);
        }

        $this->warn('reviewapp:postdeploy ran');
    }
}
