<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\EligibilityBatch;
use App\Notifications\EligibilityBatchProcessed;
use App\Practice;
use App\User;

class EligibilityBatchObserver
{
    public function creating(EligibilityBatch $eligibilityBatch)
    {
        if (auth()->check()) {
            $eligibilityBatch->initiator_id = auth()->id();
        }
    }

    public function saved(EligibilityBatch $eligibilityBatch)
    {
        if ($eligibilityBatch->isDirty('status') && 3 == $eligibilityBatch->getStatus()) {
            $practice = Practice::findOrFail($eligibilityBatch->practice_id);
            $link     = route('eligibility.batch.show', [$eligibilityBatch->id]);

            User::whereIn('email', ['mantoniou@circlelinkhealth.com', 'joe@circlelinkhealth.com'])
                ->get()
                ->map(function ($u) use ($eligibilityBatch) {
                    $u->notify(new EligibilityBatchProcessed($eligibilityBatch));
                });

            sendSlackMessage('#implementations', "Howdy! {$practice->display_name} list processing completed. ${link}");
        }
    }
}
