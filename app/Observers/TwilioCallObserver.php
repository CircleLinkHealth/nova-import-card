<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Call;
use App\TwilioCall;
use App\VoiceCall;

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

        // we don't store enrollee calls in `calls` table
        if ($twilioCall->inbound_enrollee_id) {
            return;
        }

        if ( ! in_array($twilioCall->call_status, ['completed', 'cancelled'])) {
            return;
        }

        /** @var Call $call */
        $call = Call::whereBetween('called_date', [$twilioCall->created_at->copy()->subHours(2), $twilioCall->created_at->copy()->addHours(2)])
            ->where('inbound_cpm_id', '=', $twilioCall->inbound_user_id)
            ->where('outbound_cpm_id', '=', $twilioCall->outbound_user_id)
            ->first();

        if ($call) {
            VoiceCall::updateOrCreate([
                'call_id'             => $call->id,
                'voice_callable_id'   => $twilioCall->id,
                'voice_callable_type' => TwilioCall::class,
            ]);
        }
    }
}
