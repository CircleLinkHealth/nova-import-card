<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\PatientUnsuccessfulCallReplyNotification;
use App\PostmarkInboundMail;
use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPostmarkInboundMailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var bool|null
     */
    private $dbRecordId;

    /**
     * @var array
     */
    private $input;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $input, int $dbRecordId = null)
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
        // 0. store request data in postmark_inbound_mail
        $recordId = $this->dbRecordId ?: $this->storeRawLogs();

        // 1. read source email, find patient
        $email = $this->input['From'];
        /** @var User $user */
        $user = User::whereEmail($email)->first();

        if ( ! $user) {
            sendSlackMessage('#carecoach_ops', "Could not find patient from inbound mail. See database record id[$recordId]");

            return;
        }

        try {
            // 2. create call for nurse with ASAP flag
            /** @var SchedulerService $service */
            $service = app(SchedulerService::class);
            $task    = $service->scheduleAsapCallbackTask($user, $this->input['TextBody'], 'postmark_inbound_mail');
        } catch (\Exception $e) {
            sendSlackMessage('#carecoach_ops', "{$e->getMessage()}. See database record id[$recordId]");

            return;
        }

        //if we already have a db record, we don't have to send a reply again
        if ($this->dbRecordId) {
            return;
        }

        /** @var User $careCoach */
        $careCoach = User::without(['roles', 'perms'])
            ->where('id', '=', $task->outbound_cpm_id)
            ->select(['id', 'first_name'])
            ->first();

        // 3. reply to patient
        $user->notify(new PatientUnsuccessfulCallReplyNotification($careCoach->first_name, ['mail']));
    }

    private function storeRawLogs(): ?int
    {
        try {
            $result = PostmarkInboundMail::create([
                'data' => json_encode($this->input),
            ]);

            return $result->id;
        } catch (\Throwable $e) {
            Log::warning($e->getMessage());

            return null;
        }
    }
}
