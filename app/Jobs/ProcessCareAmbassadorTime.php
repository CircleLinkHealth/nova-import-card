<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\CareAmbassadorLog;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCareAmbassadorTime implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var array
     */
    private $activity;

    private $userId;

    /**
     * Create a new job instance.
     *
     * @param $userId
     */
    public function __construct($userId, array $activity)
    {
        $this->userId   = $userId;
        $this->activity = $activity;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::with(['careAmbassador'])
            ->findOrFail($this->userId);

        if (isset($this->activity['enrolleeId'])) {
            $enrolleeId = $this->activity['enrolleeId'];

            $enrollee = Enrollee::find($enrolleeId);
            if ($enrollee) {
                $enrollee->total_time_spent += $this->activity['duration'];
                $enrollee->save();
            }
        }

        $report                       = CareAmbassadorLog::createOrGetLogs($user->careAmbassador->id);
        $report->total_time_in_system = PageTimer::where('provider_id', '=', $user->id)
            ->where('start_time', '>=', Carbon::now()->startOfDay())
            ->where('start_time', '<', Carbon::now()->endOfDay())
            ->where('end_time', '>', Carbon::now()->startOfDay())
            ->where('end_time', '<=', Carbon::now()->endOfDay())
            ->sum('duration');
        $report->save();
    }
}
