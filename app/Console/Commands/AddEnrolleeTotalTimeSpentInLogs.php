<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddEnrolleeTotalTimeSpentInLogs extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate total time in system on enrollees by care ambassadors.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollees:calculate-total-time {enrolleeId?}';

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
        $enrolleesProcessed = 0;
        $enrolleesupdated   = 0;

        $query = DB::table('lv_page_timer')
            ->select(DB::raw('sum(duration) as duration, enrollee_id'));

        $targetEnrolleeId = $this->argument('enrolleeId');
        if ($targetEnrolleeId) {
            $query->where('enrollee_id', '=', $targetEnrolleeId);
        } else {
            $query->whereNotNull('enrollee_id');
        }

        $query
            ->groupBy(DB::raw('enrollee_id'))
            ->get()
            ->keyBy('enrollee_id')
            ->each(function ($entry, $enrolleeId) use (&$enrolleesProcessed, &$enrolleesupdated) {
                // add this time in care_ambassador_logs
                ++$enrolleesProcessed;
                $enrollee = Enrollee::find($enrolleeId);
                if ( ! $enrollee) {
                    return;
                }
                $timeTrackerDuration = intval($entry->duration);
                if ($timeTrackerDuration > $enrollee->total_time_spent) {
                    $enrollee->total_time_spent = $timeTrackerDuration;
                }
                if ($enrollee->isDirty()) {
                    $enrollee->save();
                    ++$enrolleesupdated;
                }
            });

        $this->info("Processed: $enrolleesProcessed. Updated: $enrolleesupdated");
    }
}
