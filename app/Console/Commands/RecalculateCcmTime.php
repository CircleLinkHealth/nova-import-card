<?php

namespace App\Console\Commands;

use App\Activity;
use App\Patient;
use App\Services\ActivityService;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecalculateCcmTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccm_time:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Goes through activities for this month and recalculates CCM Time.';

    /**
     * Activity Service Instance
     *
     * @var ActivityService
     */
    protected $service;

    /**
     * Create a new command instance.
     *
     * @param ActivityService $service
     */
    public function __construct(ActivityService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userIds = User::ofType('participant')
            ->pluck('id')
            ->all();

        $this->service->processMonthlyActivityTime($userIds);

        $this->info('CCM Time recalculated!');
    }
}
