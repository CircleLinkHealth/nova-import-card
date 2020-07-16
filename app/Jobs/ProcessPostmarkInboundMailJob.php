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
        // 0. store request data in postmark_inbound_mail
        $recordId = $this->storeRawLogs();

        // 1. read source email, find patient
        $email = $this->input['From'];
        /** @var User $user */
        $user = User::whereEmail($email)->first();

        if ( ! $user) {
            sendSlackMessage('#carecoach_ops', "Could not find patient from inbound mail. See database record id[$recordId]");

            return;
        }

        // 2. create call for nurse with ASAP flag
        $htmlStripped = htmlspecialchars($this->input['HtmlBody'], ENT_NOQUOTES);
        /** @var SchedulerService $service */
        $service = app(SchedulerService::class);
        $service->scheduleAsapCallbackTask($user, $htmlStripped, 'postmark_inbound_mail');

        // 3. reply to patient
        $user->notify(new PatientUnsuccessfulCallReplyNotification(['mail']));
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
