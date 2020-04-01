<?php

namespace App\Console\Commands;

use App\UserTotalTimeChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckUserTotalTimeTracked extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:time-tracked {userId?} {refDate?}';

    /**
     * - Daily: For the current day, send slack alert if total CPM time of care coach has exceeded 8 hours.
     * - Every Tuesday and Friday: For the last 7 days (weekends included),
     *                           send slack alert if total CPM time of care coach has exceeded
     *                           total time committed (for those 7 days) * 1.2.
     *
     * @var string
     */
    protected $description = 'Check time tracked for the day and alert CPM admins if abnormal';

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
     * @return void
     */
    public function handle()
    {
        $refDate = $this->argument('refDate');
        if ($refDate) {
            $refDate = Carbon::parse($refDate);
        }
        if ( ! $refDate) {
            $refDate = now();
        }

        $checkAccumulatedTime = true;//$refDate->isTuesday() || $refDate->isFriday();
        $weekAgoFromYesterday = $refDate->copy()->addWeek(-1)->startOfDay();
        $yesterday            = $refDate->copy()->subDay()->endOfDay();

        $userId = $this->argument('userId');

        $alerts = (new UserTotalTimeChecker($weekAgoFromYesterday, $yesterday, $checkAccumulatedTime, $userId))->check();
        //TODO: go through alerts and send to slack

        $this->info('Done!');
    }
}
