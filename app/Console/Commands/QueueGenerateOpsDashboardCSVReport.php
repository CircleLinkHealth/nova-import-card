<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\GenerateOpsDashboardCSVReport;
use App\User;
use Illuminate\Console\Command;

class QueueGenerateOpsDashboardCSVReport extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command queues a job to produce a CSV with data from the ops dashboard, from the previous dat at 23:00 up until the job runs. This is mainly used for testing.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ops:csv';

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
        $user = User::find(8935);
        GenerateOpsDashboardCSVReport::dispatch($user);
    }
}
