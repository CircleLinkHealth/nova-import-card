<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\CareAmbassadorLog;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCareAmbassadorLogs implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $careAmbassadorUser;
    protected $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $careAmbassadorUser, Carbon $date)
    {
        $this->careAmbassadorUser = $careAmbassadorUser;
        $this->date               = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $careAmbassadorModel = $this->careAmbassadorUser->careAmbassador;
        $userId              = $this->careAmbassadorUser->id;

        if ( ! $careAmbassadorModel) {
            Log::critical("Care Ambassador model not found for User with ID: {$this->careAmbassadorUser->id}");

            return;
        }

        $log = CareAmbassadorLog::createOrGetLogs($careAmbassadorModel->id, $this->date);

        $startDateTime = $this->date->copy()->startOfDay();
        $endDateTime   = $this->date->copy()->endOfDay();

        $enrollees = Enrollee::where('care_ambassador_user_id', $userId)
            ->where('last_attempt_at', '>=', $startDateTime)
            ->where('last_attempt_at', '<=', $endDateTime)
            ->get();

        $log->total_calls = $enrollees
            ->whereIn('status', [
                Enrollee::UNREACHABLE,
                Enrollee::CONSENTED,
                Enrollee::ENROLLED,
                Enrollee::REJECTED,
                Enrollee::SOFT_REJECTED,
            ])
            ->count();

        $log->no_enrolled = $enrollees
            ->whereIn('status', [Enrollee::CONSENTED, Enrollee::ENROLLED])
            ->count();

        $log->no_rejected = $enrollees
            ->where('status', Enrollee::REJECTED)
            ->count();

        $log->no_soft_rejected = $enrollees
            ->where('status', Enrollee::SOFT_REJECTED)
            ->count();

        $log->no_utc = $enrollees
            ->where('status', Enrollee::UNREACHABLE)
            ->count();

        $log->save();
    }
}
