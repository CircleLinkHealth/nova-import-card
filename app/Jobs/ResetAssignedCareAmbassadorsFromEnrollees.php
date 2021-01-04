<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\Core\Traits\ScoutMonitoredDispatchable as Dispatchable;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResetAssignedCareAmbassadorsFromEnrollees implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle()
    {
        //unassign care ambassadors from enrollees so they can appear in the list to be assigned the next day
        Enrollee::whereNotIn('status', [Enrollee::INELIGIBLE, Enrollee::CONSENTED])
            ->update([
                'care_ambassador_user_id' => null, ]);
    }
}
