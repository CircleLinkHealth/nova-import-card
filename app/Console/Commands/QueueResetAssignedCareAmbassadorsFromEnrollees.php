<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\ResetAssignedCareAmbassadorsFromEnrollees;
use CircleLinkHealth\Customer\CpmConstants;
use Illuminate\Console\Command;

class QueueResetAssignedCareAmbassadorsFromEnrollees extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unassigns Care Ambassadors from enrollees (daily)';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollees:resetCareAmbassadors';

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
        ResetAssignedCareAmbassadorsFromEnrollees::dispatch()->onQueue(getCpmQueueName(CpmConstants::LOW_QUEUE));
    }
}
