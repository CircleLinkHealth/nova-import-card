<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\EraseTestEnrollees as EraseTestEnrolleesJob;
use Illuminate\Console\Command;

class EraseTestEnrollees extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Erase all test Enrollees (created by seeder)';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollees:erase-test';

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
        EraseTestEnrolleesJob::dispatch();
    }
}
