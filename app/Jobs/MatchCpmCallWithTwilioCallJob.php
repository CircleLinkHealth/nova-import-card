<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use CircleLinkHealth\SharedModels\Entities\Call;
use App\TwilioCall;
use App\VoiceCall;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MatchCpmCallWithTwilioCallJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Call $call;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Call $call)
    {
        $this->call = $call;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $calledDate = Carbon::parse($this->call->called_date);
        TwilioCall::where('inbound_user_id', '=', $this->call->inbound_cpm_id)
            ->where('outbound_user_id', '=', $this->call->outbound_cpm_id)
            ->whereBetween('created_at', [$calledDate->copy()->subHours(2), $calledDate->copy()->addHours(2)])
            ->each(function ($twilioCall) {
                VoiceCall::updateOrCreate([
                    'call_id'             => $this->call->id,
                    'voice_callable_id'   => $twilioCall->id,
                    'voice_callable_type' => TwilioCall::class,
                ]);
            });
    }
}
