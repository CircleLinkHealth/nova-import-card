<?php

namespace App\Console\Commands;

use App\UserTotalTimeChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CheckUserTotalTimeTracked extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:time-tracked {userId?} {refDate?} {--force-to-slack}';

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

        $isTesting            = $this->option('force-to-slack');
        $checkAccumulatedTime = $isTesting || $refDate->isTuesday() || $refDate->isFriday();
        $weekAgoFromYesterday = $refDate->copy()->addWeek(-1)->startOfDay();
        $yesterday            = $refDate->copy()->subDay()->endOfDay();

        $userId = $this->argument('userId');

        $alerts = (new UserTotalTimeChecker($weekAgoFromYesterday, $yesterday, $checkAccumulatedTime,
            $userId))->check();
        $msg    = $this->buildSlackMessage($alerts);
        if ( ! empty($msg)) {
            if ($isTesting) {
                $msg .= "\n[test]\n";
            }
            sendSlackMessage('#carecoach_ops', $msg, $isTesting);
        }

        $this->info('Done!');
    }

    /**
     * @param Collection $alerts
     *
     * @return string
     */
    private function buildSlackMessage(Collection $alerts)
    {
        $result = '';
        /** @var Collection $daily */
        $daily = $alerts->get('daily');
        if ($daily) {
            $maxHours = UserTotalTimeChecker::getMaxHoursForDay();
            $result   .= "Warning: The following nurses have exceeded the daily maximum of $maxHours hours:\n";
            $daily->each(function ($time, $id) use (&$result) {
                $rounded = round($time, 2);
                $result  .= "Nurse[$id]: $rounded hrs spent in CPM yesterday\n";
            });
        }
        $weekly = $alerts->get('weekly');
        if ($weekly) {
            $timesMore = UserTotalTimeChecker::getThresholdForWeek();
            if ( ! empty($result)) {
                $result .= "\n\n";
            }
            $result .= "Warning: The following nurses have exceeded their committed hours for the last 7 days by more than {$timesMore}x:\n";
            $weekly->each(function ($time, $id) use (&$result) {
                $rounded = round($time, 2);
                $result  .= "Nurse[$id]: $rounded hrs spent in CPM last 7 days\n";
            });
        }

        return $result;
    }
}
