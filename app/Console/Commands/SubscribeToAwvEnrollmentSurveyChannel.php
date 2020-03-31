<?php

namespace App\Console\Commands;

use App\Jobs\EnrollableSurveyCompleted;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class SubscribeToAwvEnrollmentSurveyChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:enrollmentCompleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to enrollable survey completed channel';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $channel = 'enrollable-survey-completed';
        $this->info("Listening on $channel");

        Redis::connection('pub_sub')->subscribe([$channel], function ($data) {
            $this->info("Enrollment Completed For" . $data);
            EnrollableSurveyCompleted::dispatch($data)->onQueue('low');
        });
    }
}
