<?php

namespace App\Console\Commands;

use App\Jobs\ResetAssignedCareAmbassadorsFromEnrollees;
use Illuminate\Console\Command;

class QueueResetAssignedCareAmbassadorsFromEnrollees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollees:resetCareAmbassadors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unassigns Care Ambassadors from enrollees (daily)';

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
        ResetAssignedCareAmbassadorsFromEnrollees::dispatch()->onQueue('low');
    }
}
