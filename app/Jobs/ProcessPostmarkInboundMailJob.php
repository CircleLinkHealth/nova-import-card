<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Entities\EmailAddressParts;
use App\Entities\PostmarkInboundMailRequest;
use App\Http\Controllers\Postmark\PostmarkInboundCallbackMatchResults;
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
use Illuminate\Support\Str;

class ProcessPostmarkInboundMailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const FROM_CALLBACK_EMAIL_USERNAME = 'callcenterusa.net';
    const FROM_CALLBACK_FULL_EMAIL     = 'message.dispatch@callcenterusa.net';

    public int $tries = 1;

    /**
     * @var bool|null
     */
    private $dbRecordId;

    private PostmarkInboundMailRequest $input;

    /**
     * Create a new job instance.
     */
    public function __construct(PostmarkInboundMailRequest $input, int $dbRecordId = null)
    {
        $this->input      = $input;
        $this->dbRecordId = $dbRecordId;
    }

    /**
     * @return string|void
     */
    public function handle()
    {
        // 0. store request data in postmark_inbound_mail
        $recordId = $this->dbRecordId ?: $this->storeRawLogs();

        // 1. read source email, find patient
        $email      = $this->removeAliasFromEmail($this->input->From);
        $emailParts = $this->splitEmail($email);
        $users      = User::where('email', 'REGEXP', '^'.$emailParts->username.'[+|@]')
            ->where('email', 'REGEXP', $emailParts->domain.'$');

        if ( ! $email) {
            Log::error("Empty Postmark notification field:'From'. Record id $recordId");
        }

        if (self::FROM_CALLBACK_EMAIL_USERNAME === $emailParts->username) {
            try {
                $postmarkMarkService  = (new PostmarkCallbackMailService());
                $postmarkCallbackData = $postmarkMarkService->postmarkInboundData($recordId);
                /** @var array $matchedResultsFromDB */
                $matchedResultsFromDB = (new PostmarkInboundCallbackMatchResults($postmarkCallbackData, $recordId, $postmarkMarkService))
                    ->getMatchedPatients();

                if (empty($matchedResultsFromDB)) {
                    Log::warning("Could not find a patient match for record_id:[$recordId] in postmark_inbound_mail");
                    sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient match for record_id:[$recordId] in postmark_inbound_mail");

                    return;
                }

                if ($postmarkMarkService->shouldCreateCallBackFromPostmarkInbound($matchedResultsFromDB)) {
                    /** @var SchedulerService $service */
                    $service = app(SchedulerService::class);
                    $service->scheduleAsapCallbackTask(
                        $matchedResultsFromDB['matchResult'],
                        $postmarkCallbackData['Msg'],
                        'postmark_inbound_mail',
                        null,
                        SchedulerService::CALL_BACK_TYPE
                    );

                    //@todo: Send Live Notification. It should be done by Call observer already. Check!
                    return;
                }

                return;
            } catch (\Exception $e) {
                if (Str::contains($e->getMessage(), SchedulerService::NURSE_NOT_FOUND)) {
                    //                Assign to CA's ???
                    return;
                }
                Log::error($e->getMessage());
                sendSlackMessage('#carecoach_ops_alerts', "{$e->getMessage()}. See database record id[$recordId]");

                return;
            }
        }

        /** @var User $user */
        $user = User::whereEmail($email)
            ->with([
                'primaryPractice' => function ($q) {
                    $q->select(['id', 'display_name']);
                },
            ])
            ->get();

        if ($users->isEmpty()) {
            sendSlackMessage('#carecoach_ops_alerts', "Could not find patient from inbound mail. See database record id[$recordId]");

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
            sendSlackMessage('#carecoach_ops_alerts', "Could not find unsuccessful call notification from inbound mail. See database record id[$recordId]");

            return;
        }

        try {
            $user = $users->where('id', '=', $notification->notifiable_id)->first();

            // 3. create call for nurse with ASAP flag
            /** @var SchedulerService $service */
            $service = app(SchedulerService::class);
            $task    = $service->scheduleAsapCallbackTask(
                $user,
                $this->filterEmailBody($this->input->TextBody),
                'postmark_inbound_mail',
                null,
                SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS
            );
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
        $user->notify(new PatientUnsuccessfulCallReplyNotification(optional($careCoach)->first_name, $user->primaryPractice->display_name, ['mail']));
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

    private function removeAliasFromEmail(string $email)
    {
        return preg_replace('/(\+[^\@]+)/', '', $email);
    }

    private function splitEmail(string $email)
    {
        $parts = explode('@', $email);

        return new EmailAddressParts($parts[0] ?? '', $parts[1] ?? '');
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
