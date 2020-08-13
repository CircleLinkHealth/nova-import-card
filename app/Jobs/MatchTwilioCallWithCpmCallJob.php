<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Call;
use App\TwilioCall;
use App\VoiceCall;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MatchTwilioCallWithCpmCallJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private TwilioCall $twilioCall;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TwilioCall $twilioCall)
    {
        $this->twilioCall = $twilioCall;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // we don't store enrollee calls in `calls` table
        if ($this->twilioCall->inbound_enrollee_id) {
            return;
        }

        if ( ! in_array($this->twilioCall->call_status, ['completed', 'cancelled'])) {
            return;
        }

        /** @var Call $call */
        $call = Call::whereBetween('called_date', [$this->twilioCall->created_at->copy()->subHours(2), $this->twilioCall->created_at->copy()->addHours(2)])
            ->where('inbound_cpm_id', '=', $this->twilioCall->inbound_user_id)
            ->where('outbound_cpm_id', '=', $this->twilioCall->outbound_user_id)
            ->first();

        if ($call) {
            VoiceCall::updateOrCreate([
                'call_id'             => $call->id,
                'voice_callable_id'   => $this->twilioCall->id,
                'voice_callable_type' => TwilioCall::class,
            ]);
        }
    }
}
