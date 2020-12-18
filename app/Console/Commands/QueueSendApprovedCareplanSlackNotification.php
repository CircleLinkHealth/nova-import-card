<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\SendApprovedCareplanSlackNotification;
use CircleLinkHealth\Customer\CpmConstants;
use Illuminate\Console\Command;

class QueueSendApprovedCareplanSlackNotification extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends copy of daily summary and displays the number of Approved Care Plans not yet printed';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'careplans:notifySlack';

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
        SendApprovedCareplanSlackNotification::dispatch()->onQueue(getCpmQueueName(CpmConstants::HIGH_QUEUE));
    }
}
