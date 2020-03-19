<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\AwvPatientReportNotify;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class SubscribeToRedisAWVChannel extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to a Redis channel';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:subscribe';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $channel = 'awv-patient-report-created';
        $this->info("Listening on $channel");

        Redis::subscribe([$channel], function ($patientReportdata) {
            $this->info("Received event. Will dispatch for AwvPatientReportNotify.");
            AwvPatientReportNotify::dispatch($patientReportdata);

            //this will stop the process from exiting and will re-subscribe to the channel
            $this->handle();
        });
    }
}
