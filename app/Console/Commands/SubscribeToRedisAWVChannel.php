<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\AwvPatientReportNotify;
use Illuminate\Console\Command;
use Redis;

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
     * @return mixed
     */
    public function handle()
    {
        Redis::subscribe(['test-channel'], function ($message) {
            echo $message;

            \Log::channel('logdna')->info('Recording test-channel message', [
                'batch_id' => \Carbon::now()->toDateTimeString(),
            ]);
        });

        Redis::subscribe(['awv-patient-report-created'], function ($patientReportdata) {
            echo $patientReportdata;
//            AwvPatientReportNotify::dispatch(json_decode($patientReportdata));
        });
    }
}
