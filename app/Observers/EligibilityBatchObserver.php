<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/19/18
 * Time: 8:03 PM
 */

namespace App\Observers;


use App\EligibilityBatch;
use App\Practice;

class EligibilityBatchObserver
{
    public function creating(EligibilityBatch $eligibilityBatch)
    {
        if ( ! $eligibilityBatch->stats) {
            $eligibilityBatch->stats = [
                'eligible'   => 0,
                'ineligible' => 0,
                'errors'     => 0,
                'duplicates' => 0,
            ];
        }
    }

    public function saving(EligibilityBatch $eligibilityBatch)
    {
        if ($eligibilityBatch->isDirty('status') && $eligibilityBatch->getStatus() == 3) {
            $practice = Practice::findOrFail($eligibilityBatch->practice_id);
            $link     = route('eligibility.batch.show', [$eligibilityBatch->id]);
            sendSlackMessage('#implementations', "Howdy! {$practice->display_name} list processing completed. $link");
        }
    }
}