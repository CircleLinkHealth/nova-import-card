<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\SharedModels\Entities\CareAmbassadorLog;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
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

        $report = CareAmbassadorLog::createOrGetLogs($user->careAmbassador->id);
        $report->total_time_in_system += $this->activity['duration'];
        $report->save();
    }
}
