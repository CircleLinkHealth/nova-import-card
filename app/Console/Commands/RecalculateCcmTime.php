<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Services\ActivityService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class RecalculateCcmTime extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Goes through activities for this month and recalculates CCM Time.';

    /**
     * Activity Service Instance.
     *
     * @var ActivityService
     */
    protected $service;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccm_time:recalculate {dateString? : the month we are recalculating for in format YYYY-MM-DD} {userIds? : comma separated. leave empty to recalculate for all}';

    /**
     * Create a new command instance.
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
        $userIds = $this->argument('userIds') ?? null;
        if (null != $userIds) {
            $userIds = explode(',', $userIds);
        } else {
            $userIds = User::ofType('participant')
                ->pluck('id')
                ->all();
        }

        $this->comment(count($userIds).' Users to recalculate time.');

        $date = $this->argument('dateString') ?? null;

        if ($date) {
            $date = Carbon::parse($date);
        }

        $this->service->processMonthlyActivityTime($userIds, $date);

        $this->info('CCM Time recalculated!');
    }
}
