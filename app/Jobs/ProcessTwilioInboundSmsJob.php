<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\PatientUnsuccessfulCallReplyNotification;
use App\Services\Calls\SchedulerService;
use App\TwilioInboundSms;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTwilioInboundSmsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var array
     */
    private $input;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $input)
    {
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 0. store request data in twilio_inbound_sms
        $recordId = $this->storeRawLogs();

        // 1. read source number, find patient
        $fromNumber          = $this->input['From'];
        $fromNumberFormatted = formatPhoneNumber($fromNumber);
        /** @var User $user */
        $user = User::whereHas('phoneNumbers', function ($q) use ($fromNumber, $fromNumberFormatted) {
            $q->whereIn('number', [$fromNumber, $fromNumberFormatted]);
        })->first();

        if ( ! $user) {
            sendSlackMessage('#carecoach_ops', "Could not find patient from inbound sms. See database record id[$recordId]");

            return;
        }

        // 2. create call for nurse with ASAP flag
        /** @var SchedulerService $service */
        $service = app(SchedulerService::class);
        $service->scheduleAsapCallbackTaskFromSms($user, $this->input['From'], $this->input['Body'], 'twilio_inbound_sms');

        // 3. reply to patient
        $user->notify(new PatientUnsuccessfulCallReplyNotification(['twilio']));
    }

    private function storeRawLogs(): ?int
    {
        try {
            $result = TwilioInboundSms::create([
                'data' => json_encode($this->input),
            ]);

            return $result->id;
        } catch (\Throwable $e) {
            Log::warning($e->getMessage());

            return null;
        }
    }
}
