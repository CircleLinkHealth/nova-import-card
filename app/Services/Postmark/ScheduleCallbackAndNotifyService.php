<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Entities\EmailAddressParts;
use App\Entities\PostmarkInboundMailRequest;
use App\Notifications\PatientUnsuccessfulCallNotification;
use App\Notifications\PatientUnsuccessfulCallReplyNotification;
use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Customer\Entities\User;

class ScheduleCallbackAndNotifyService
{
    public function processCallbackAndNotify(EmailAddressParts $emailParts, ?int $recordId, ?int $dbRecordId, PostmarkInboundMailRequest $input)
    {
        $users = User::where('email', 'REGEXP', '^'.$emailParts->username.'[+|@]')
            ->where('email', 'REGEXP', $emailParts->domain.'$')
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
                $this->filterEmailBody($input->TextBody),
                'postmark_inbound_mail',
                null,
                SchedulerService::SCHEDULE_NEXT_CALL_PER_PATIENT_SMS
            );
        } catch (\Exception $e) {
            sendSlackMessage('#carecoach_ops_alerts', "{$e->getMessage()}. See database record id[$recordId]");

            return;
        }

        if ($dbRecordId) {
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
}
