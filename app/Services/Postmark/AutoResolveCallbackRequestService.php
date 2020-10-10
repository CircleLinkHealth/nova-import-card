<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Jobs\ProcessPostmarkInboundMailJob;
use App\Services\Calls\SchedulerService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutoResolveCallbackRequestService
{
    public function processCreateCallback(int $recordId)
    {
        try {
            $postmarkCallbackService = app(PostmarkCallbackMailService::class);
            $postmarkCallbackData    = $postmarkCallbackService->postmarkInboundData($recordId);
            /** @var array $matchedResultsFromDB */
            $matchedResultsFromDB = (new PostmarkInboundCallbackMatchResults($postmarkCallbackData, $recordId))
                ->matchedPatientsData();

            if (empty($matchedResultsFromDB)) {
                Log::warning("Could not find a patient match for record_id:[$recordId] in postmark_inbound_mail");
                sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient match for record_id:[$recordId] in postmark_inbound_mail");

                return;
            }

            if (PostmarkInboundCallbackMatchResults::CREATE_CALLBACK === $matchedResultsFromDB['reasoning']) {
                /** @var SchedulerService $service */
                $service = app(SchedulerService::class);
                $service->scheduleAsapCallbackTask(
                    $matchedResultsFromDB['matchUsersResult'],
                    $this->constructCallbackMessage($postmarkCallbackData),
                    ProcessPostmarkInboundMailJob::SCHEDULER_POSTMARK_INBOUND_MAIL,
                    null,
                    SchedulerService::CALL_BACK_TYPE,
                );

                return;
            }

            if (PostmarkInboundCallbackMatchResults::NOT_CONSENTED_CA_ASSIGNED === $matchedResultsFromDB['reasoning']) {
                $this->assignCallbackToCareAmbassador($matchedResultsFromDB['matchUsersResult'], $recordId);
                // Will createUnresolvedInboundCallback even if NOT_CONSENTED_CA_ASSIGNED is true. We ll decide what to show in view_table.
            }
            $this->createUnresolvedInboundCallback($matchedResultsFromDB, $recordId);
        } catch (\Exception $e) {
            if (Str::contains($e->getMessage(), SchedulerService::NURSE_NOT_FOUND)) {
                Log::error($e->getMessage());
                sendSlackMessage('#carecoach_ops_alerts', "{$e->getMessage()}. See inbound_postmark_mail id [$recordId]. Nurse not found");

                return;
            }
            Log::error($e->getMessage());
            sendSlackMessage('#carecoach_ops_alerts', "{$e->getMessage()}. See inbound_postmark_mail id [$recordId]");
        }

        Log::error('Place error here');
    }

    private function assignCallbackToCareAmbassador(User $user, int $recordId)
    {
        /** @var Enrollee $enrollee */
        $enrollee = $user->enrollee;

        if ( ! $enrollee) {
            Log::critical("Enrollee for postmark inbound data rec_id: $recordId not found");

            return;
        }

        $enrollee->update([
            'status'                  => Enrollee::TO_CALL,
            'care_ambassador_user_id' => $enrollee->care_ambassador_user_id,
            'requested_callback'      => Carbon::now()->toDate(),
            'callback_note'           => htmlspecialchars('Callback automatically scheduled by the system - patient requested callback', ENT_NOQUOTES),
        ]);
    }

    private function constructCallbackMessage(array $postmarkCallbackData)
    {
        $callerId       = $postmarkCallbackData['Clr ID'];
        $fullName       = app(InboundCallbackMultimatchService::class)->parseNameFromCallerField($callerId);
        $firstName      = $fullName['firstName'];
        $lastName       = $fullName['lastName'];
        $phone          = $postmarkCallbackData['Phone'];
        $phoneFormatted = formatPhoneNumberE164($phone);
        $from           = $postmarkCallbackData['From'];
        $message        = $postmarkCallbackData['Msg'];
        $now            = Carbon::now();

        return $now->toDateString().' '.$now->format('g:i A').' '.'From'.' '."[$phoneFormatted $firstName $lastName]: $message.".' '."Callback Number: $phone";
    }

    private function createUnresolvedInboundCallback(array $matchedResultsFromDB, int $recordId)
    {
        (new ProcessUnresolvedPostmarkCallback($matchedResultsFromDB, $recordId))->handleUnresolved();
    }
}
