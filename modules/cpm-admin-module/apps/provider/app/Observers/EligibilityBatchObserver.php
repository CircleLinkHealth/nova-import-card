<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\Eligibility\Entities\EligibilityBatch;

class EligibilityBatchObserver
{
    public function creating(EligibilityBatch $eligibilityBatch)
    {
        if (auth()->check()) {
            $eligibilityBatch->initiator_id = auth()->id();
        }
    }
}
