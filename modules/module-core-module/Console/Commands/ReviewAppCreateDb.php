<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Console\Commands;

use CircleLinkHealth\Core\Traits\RunsConsoleCommands;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class ReviewAppCreateDb extends Command
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
    protected $signature = 'reviewapp:create-db';

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
     * @throws \Exception
     *
     * @return mixed
     */
    public function handle()
    {
        if ( ! app()->environment(['review', 'local', 'testing'])) {
            return;
        }

        $this->output->note('Running reviewapp:create-db');

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
            $this->runCpmCommand(['php', 'artisan', '-vvv', $cmd, $dbName]);
        }

        $this->warn('reviewapp:create-db ran');
    }
}
