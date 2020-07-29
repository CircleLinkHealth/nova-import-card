<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\PatientUnsuccessfulCallNotification;
use App\Notifications\PatientUnsuccessfulCallReplyNotification;
use App\Services\Calls\SchedulerService;
use App\TwilioInboundSms;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
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
            sendSlackMessage('#carecoach_ops_alerts', "Could not find patient from inbound sms. See database record id[$recordId]");

            return;
        }

        // 2. check that we have sent an unsuccessful call notification to this patient in the last 2 weeks
        $hasNotification = DatabaseNotification::whereType(PatientUnsuccessfulCallNotification::class)
            ->whereIn('notifiable_type', [User::class, \App\User::class])
            ->where('notifiable_id', '=', $user->id)
            ->where('created_at', '>=', now()->subDays(14))
            ->exists();

        if ( ! $hasNotification) {
            sendSlackMessage('#carecoach_ops_alerts', "Could not find unsuccessful call notification from inbound sms. See database record id[$recordId]");

            return;
        }

        try {
            // 3. create call for nurse with ASAP flag
            /** @var SchedulerService $service */
            $service = app(SchedulerService::class);
            $task    = $service->scheduleAsapCallbackTaskFromSms($user, $this->input['From'], $this->input['Body'], 'twilio_inbound_sms');
        } catch (\Exception $e) {
            sendSlackMessage('#carecoach_ops_alerts', "{$e->getMessage()}. See database record id[$recordId]");

            return;
        }

        /** @var User $careCoach */
        $careCoach = User::without(['roles', 'perms'])
            ->where('id', '=', $task->outbound_cpm_id)
            ->select(['id', 'first_name'])
            ->first();

        // 4. reply to patient
        $user->notify(new PatientUnsuccessfulCallReplyNotification($careCoach->first_name, ['twilio']));
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
