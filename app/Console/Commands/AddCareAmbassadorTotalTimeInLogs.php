<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\SharedModels\Entities\CareAmbassadorLog;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddCareAmbassadorTotalTimeInLogs extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate total time in system per day for care ambassadors.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'care-ambassadors:calculate-total-time {userId?}';

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
        $processed = 0;
        $updated   = 0;

        // get users of type care ambassador
        $userId = $this->argument('userId');
        if ($userId) {
            $userIds = [$userId];
        } else {
            $userIds = User::ofType('care-ambassador')
                ->pluck('id')
                ->all();
        }

        // get their time grouped by day from lv_page_timer
        DB::table('lv_page_timer')
            ->select(DB::raw('MAKEDATE(YEAR(start_time),DAYOFYEAR(start_time)) as date, sum(duration) as duration, care_ambassadors.id as care_ambassador_id'))
            ->join('care_ambassadors', 'lv_page_timer.provider_id', '=', 'care_ambassadors.user_id')
            ->whereIn('provider_id', $userIds)
            ->groupBy(DB::raw('date'))
            ->groupBy(DB::raw('care_ambassador_id'))
            ->orderBy(DB::raw('date'))
            ->get()
            ->keyBy('date')
            ->each(function ($entry, $date) use (&$processed, &$updated) {
                // add this time in care_ambassador_logs
                ++$processed;
                $report = CareAmbassadorLog::createOrGetLogs($entry->care_ambassador_id, Carbon::parse($date));
                $timeTrackerDuration = intval($entry->duration);
                if ($timeTrackerDuration > $report->total_time_in_system) {
                    $report->total_time_in_system = $timeTrackerDuration;
                }
                if ($report->isDirty()) {
                    $report->save();
                    ++$updated;
                }
            });

        $this->info("Processed: $processed. Updated: $updated");
    }
}
