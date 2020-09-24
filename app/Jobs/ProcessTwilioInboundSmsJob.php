<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Entities\TwilioInboundSmsRequest;
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

    public int $tries = 1;

    /**
     * @var bool|null
     */
    private $dbRecordId;

    private TwilioInboundSmsRequest $input;
                                
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TwilioInboundSmsRequest $input, int $dbRecordId = null)
    {
        $this->input      = $input;
        $this->dbRecordId = $dbRecordId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 0. store request data in twilio_inbound_sms
        $recordId = $this->dbRecordId ?: $this->storeRawLogs();

        // 1. read source number, find patient
        $fromNumber          = $this->input->From;
        $fromNumberFormatted = formatPhoneNumber($fromNumber);
        $users               = User::whereHas('phoneNumbers', function ($q) use ($fromNumber, $fromNumberFormatted) {
            $q->whereIn('number', [$fromNumber, $fromNumberFormatted]);
        })
            ->with([
                'primaryPractice' => function ($q) {
                    $q->select(['id', 'display_name']);
                },
            ])
            ->get();

        if ($users->isEmpty()) {
            sendSlackMessage('#carecoach_ops_alerts', "Could not find patient from inbound sms. See database record id[$recordId]");

            return;
        }

        // 2. check that we have sent an unsuccessful call notification to this patient in the last 2 weeks
        $notification = DatabaseNotification::whereType(PatientUnsuccessfulCallNotification::class)
            ->whereIn('notifiable_type', [User::class, \App\User::class])
            ->whereIn('notifiable_id', $users->map(fn ($user) => $user->id)->toArray())
            ->where('created_at', '>=', now()->subDays(14))
            ->orderByDesc('created_at')
            ->first();

        if ( ! $notification) {
            sendSlackMessage('#carecoach_ops_alerts', "Could not find unsuccessful call notification from inbound sms. See database record id[$recordId]");

            return;
        }

        try {
            $user = $users->where('id', '=', $notification->notifiable_id)->first();

            // 3. create call for nurse with ASAP flag
            /** @var SchedulerService $service */
            $service = app(SchedulerService::class);
            $task    = $service->scheduleAsapCallbackTaskFromSms($user, $this->input->From, $this->input->Body, 'twilio_inbound_sms');
        } catch (\Exception $e) {
            sendSlackMessage('#carecoach_ops_alerts', "{$e->getMessage()}. See database record id[$recordId]");

            return;
        }

        if ($this->dbRecordId) {
            return;
        }

        /** @var User $careCoach */
        $careCoach = User::without(['roles', 'perms'])
            ->where('id', '=', $task->outbound_cpm_id)
            ->select(['id', 'first_name'])
            ->first();

        // 4. reply to patient
        $user->notify(new PatientUnsuccessfulCallReplyNotification(optional($careCoach)->first_name, $user->primaryPractice->display_name, ['twilio']));
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
