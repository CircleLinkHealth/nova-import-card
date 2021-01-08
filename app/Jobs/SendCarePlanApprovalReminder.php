<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Notifications\CarePlanApprovalReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCarePlanApprovalReminder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    public int $numberOfCareplans;

    public int $userId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $userId, int $numberOfCareplans)
    {
        $this->userId            = $userId;
        $this->numberOfCareplans = $numberOfCareplans;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::findOrFail($this->userId);

        $user->loadMissing(['primaryPractice.settings']);

        $user->notify(new CarePlanApprovalReminder($this->numberOfCareplans));
    }
}
