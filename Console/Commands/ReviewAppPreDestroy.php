<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ReviewAppPreDestroy extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commands to run on event pr-predestroy of a Heroku review app.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviewapp:predestroy';

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
        Schema::getConnection()->getDoctrineSchemaManager()->dropDatabase("`{$dbName}`");

        $this->line('reviewapp:predestroy ran');
    }
}
