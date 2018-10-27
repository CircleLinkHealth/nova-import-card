<?php

namespace App\Console\Commands;

use App\Jobs\SendApprovedCareplanSlackNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class QueueSendApprovedCareplanSlackNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'careplans:notifySlack';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends copy of daily summary and displays the number of Approved Care Plans not yet printed';

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
        SendApprovedCareplanSlackNotification::dispatch()->onQueue('high');
    }
}
