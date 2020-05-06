<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Jobs\ListenToAwvChannel;
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
        $this->info('Listening...');
        Redis::connection('pub_sub')->subscribe(
            [
                ListenToAwvChannel::AWV_REPORT_CREATED,
                ListenToAwvChannel::ENROLLMENT_SURVEY_COMPLETED,
            ],
            function ($data, $channel) {
                $this->info("Received on $channel");
                ListenToAwvChannel::dispatch($data, $channel);
            }
        );
    }
}
