<?php

namespace App\Jobs;

use App\CareAmbassadorLog;
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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId;

    /**
     * @var array
     */
    private $activity;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param array $activity
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

        $enrolleeId = $this->activity['enrolleeId'];

        $enrollee = Enrollee::find($enrolleeId);
        if ($enrollee) {
            $enrollee->total_time_spent += $this->activity['duration'];
            $enrollee->save();
        }


        $report                       = CareAmbassadorLog::createOrGetLogs($user->careAmbassador->id);
        $report->total_time_in_system = PageTimer::where('provider_id', '=', $user->id)
                                                 ->sum('duration');
        $report->save();
    }
}
