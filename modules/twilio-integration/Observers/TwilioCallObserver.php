<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwilioIntegration\Observers;

use CircleLinkHealth\TwilioIntegration\Jobs\MatchTwilioCallWithCpmCallJob;
use CircleLinkHealth\TwilioIntegration\Models\TwilioCall;

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
