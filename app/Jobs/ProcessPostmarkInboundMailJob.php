<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Notifications\PatientUnsuccessfulCallNotification;
use App\Notifications\PatientUnsuccessfulCallReplyNotification;
use App\PostmarkInboundMail;
use App\Services\Calls\SchedulerService;
use App\Services\Postmark\PostmarkCallbackMailService;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
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
    const FROM_CALLBACK_EMAIL = 'message.dispatch@callcenterusa.net';

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

        if ( ! $email) {
            Log::error("Empty Postmark notification field:'From'. Record id $recordId");
        }

        if (self::FROM_CALLBACK_EMAIL === $email) {
            try {
                $postmarkMarkService  = (new PostmarkCallbackMailService());
                $postmarkCallbackData = $postmarkMarkService->parsedEmailData($recordId);
                /** @var User $matchedPatient */
                $matchedPatient = $postmarkMarkService->getMatchedPatient($postmarkCallbackData, $recordId);
                /** @var SchedulerService $service */
                $service = app(SchedulerService::class);
                $task    = $service->scheduleAsapCallbackTask(
                    $matchedPatient,
                    $postmarkCallbackData['Msg'],
                    'postmark_inbound_mail',
                    null,
                    SchedulerService::CALL_BACK_TYPE
                );

                return;
//                    Send Live Notification
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                sendSlackMessage('#carecoach_ops_alerts', "{$e->getMessage()}. See database record id[$recordId]");
//                Assign to CA's

                return;
            }

            return;
        }

        /** @var User $user */
        $user = User::whereEmail($email)
            ->with([
                'primaryPractice' => function ($q) {
                    $q->select(['id', 'display_name']);
                },
            ])
            ->first();

        if ( ! $user) {
            sendSlackMessage('#carecoach_ops_alerts', "Could not find patient from inbound mail. See database record id[$recordId]");

            return;
        }

        // 2. check that we have sent an unsuccessful call notification to this patient in the last 2 weeks
        $hasNotification = DatabaseNotification::whereType(PatientUnsuccessfulCallNotification::class)
            ->whereIn('notifiable_type', [User::class, \App\User::class])
            ->where('notifiable_id', '=', $user->id)
            ->where('created_at', '>=', now()->subDays(14))
            ->exists();

        if ( ! $hasNotification) {
            sendSlackMessage('#carecoach_ops_alerts', "Could not find unsuccessful call notification from inbound mail. See database record id[$recordId]");

            return;
        }

        try {
            // 3. create call for nurse with ASAP flag
            /** @var SchedulerService $service */
            $service = app(SchedulerService::class);
            $task    = $service->scheduleAsapCallbackTask(
                $user,
                $this->filterEmailBody($this->input['TextBody']),
                'postmark_inbound_mail',
                null,
                SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS
            );
        } catch (\Exception $e) {
            sendSlackMessage('#carecoach_ops_alerts', "{$e->getMessage()}. See database record id[$recordId]");

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

        // 4. reply to patient
        $user->notify(new PatientUnsuccessfulCallReplyNotification($careCoach->first_name, $user->primaryPractice->display_name, ['mail']));
    }

    private function filterEmailBody($emailBody): string
    {
        $textBodyParsed = \EmailReplyParser\EmailReplyParser::read($emailBody);
        $text           = '';
        foreach ($textBodyParsed->getFragments() as $fragment) {
            if ($fragment->isQuoted()) {
                continue;
            }
            $text .= $fragment->getContent();
        }

        return $text;
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
