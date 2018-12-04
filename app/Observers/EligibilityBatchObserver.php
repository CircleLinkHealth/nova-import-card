<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/19/18
 * Time: 8:03 PM
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
        if ($eligibilityBatch->isDirty('status') && $eligibilityBatch->getStatus() == 3) {
            $practice = Practice::findOrFail($eligibilityBatch->practice_id);
            $link     = route('eligibility.batch.show', [$eligibilityBatch->id]);


            User::whereIn('email', ['mantoniou@circlelinkhealth.com', 'joe@circlelinkhealth.com'])
                ->get()
                ->map(function ($u) use ($eligibilityBatch) {
                    $u->notify(new EligibilityBatchProcessed($eligibilityBatch));
                });

            sendSlackMessage('#implementations', "Howdy! {$practice->display_name} list processing completed. $link");
        }
    }
}
