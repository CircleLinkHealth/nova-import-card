<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Jobs\MatchTwilioCallWithCpmCallJob;
use CircleLinkHealth\SharedModels\Entities\TwilioCall;

class TwilioCallObserver
{
    public function saved(TwilioCall $twilioCall)
    {
        $this->matchWithCpmCall($twilioCall);
    }

    private function matchWithCpmCall(TwilioCall $twilioCall)
    {
        if ( ! $twilioCall->isDirty('call_status')) {
            return;
        }

        MatchTwilioCallWithCpmCallJob::dispatch($twilioCall);
    }
}
