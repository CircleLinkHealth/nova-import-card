<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Postmark;

use Carbon\Carbon;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\DTO\PostmarkCallbackInboundData;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Services\Postmark\PostmarkInboundCallbackMatchResults;
use CircleLinkHealth\SharedModels\DTO\AutomatedCallbackMessageValueObject;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundCallbackRequest;
use CircleLinkHealth\SharedModels\Services\SchedulerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutoResolveCallbackRequestService
{
    public function processCreateCallback(int $recordId)
    {
        try {
            $postmarkCallbackService = app(PostmarkCallbackMailService::class);
            $postmarkCallbackData    = $postmarkCallbackService->postmarkInboundData($recordId);
            $matchedResultsFromDB    = (new PostmarkInboundCallbackMatchResults($postmarkCallbackData, $recordId))
                ->matchedPatientsData();

            if (empty($matchedResultsFromDB)) {
                $mainMessage = "Could not find a patient match for record_id:[$recordId] in postmark_inbound_mail";
                Log::warning($mainMessage);
                $testingSlack = forceSendSlackNotifications();
                $slackMessage = $testingSlack ? $mainMessage.' '.'Please Ignore this post [TESTING]' : $mainMessage;
                sendSlackMessage('#carecoach_ops_alerts', $slackMessage, $testingSlack);

                return;
            }

            if (PostmarkInboundCallbackMatchResults::CREATE_CALLBACK === $matchedResultsFromDB->reasoning()) {
                $callBackAssignedToNurse = $this->assignCallbackToNurse($matchedResultsFromDB->matchedData(), $postmarkCallbackData);
                if ( ! $callBackAssignedToNurse) {
                    Log::error("Failed to created callback assigned to Nurse. See [$recordId] in inbound_postmark_mail");
                    sendSlackMessage('#carecoach_ops_alerts', "Failed to created callback assigned to Nurse. See [$recordId] in inbound_postmark_mail");
                }

                return;
            }

            if (PostmarkInboundCallbackMatchResults::NOT_CONSENTED_CA_ASSIGNED === $matchedResultsFromDB->reasoning()) {
                $callBackAssignedToCa = $this->assignCallbackToCareAmbassador($matchedResultsFromDB->matchedData(), $recordId);
                if ( ! $callBackAssignedToCa) {
                    Log::error("Failed to created callback assigned to CA. See [$recordId] in inbound_postmark_mail");
                    sendSlackMessage('#carecoach_ops_alerts', "Failed to created callback assigned to CA. See [$recordId] in inbound_postmark_mail");
                }

                return;
            }

            $saveUnresolvedCallback = $this->createUnresolvedInboundCallback($matchedResultsFromDB->toArray(), $recordId);

            if ( ! $saveUnresolvedCallback) {
                Log::error("Failed to create Unresolved Callback for:[$recordId] in postmark_inbound_mail");
                sendSlackMessage('#carecoach_ops_alerts', "Failed to create Unresolved Callback for:[$recordId] in postmark_inbound_mail");
            }

            return;
        } catch (\Exception $e) {
            if (Str::contains($e->getMessage(), SchedulerService::NURSE_NOT_FOUND)) {
                Log::error($e->getMessage());
                sendSlackMessage('#carecoach_ops_alerts', "{$e->getMessage()}. See inbound_postmark_mail id [$recordId]. Nurse not found");

                return;
            }
            if (Str::contains($e->getMessage(), PostmarkInboundCallbackRequest::INBOUND_CALLBACK_DAILY_REPORT)) {
                sendSlackMessage(
                    '#carecoach_ops_alerts',
                    "[$recordId] in postmark_inbound_mail, has lot of emails in body. Probably it is the daily callback report."
                );

                return;
            }

            Log::error($e->getMessage());
            sendSlackMessage('#carecoach_ops_alerts', "{$e->getMessage()}. See inbound_postmark_mail id [$recordId]");
        }

        Log::error("Unexpected error. Should have not reached here. See $recordId.");
        sendSlackMessage('#carecoach_ops_alerts', "{Unexpected error. See inbound_postmark_mail id [$recordId]");
    }

    /**
     * @return bool
     */
    private function assignCallbackToCareAmbassador(User $user, int $recordId)
    {
        /** @var Enrollee $enrollee */
        $enrollee = $user->enrollee;

        if ( ! $enrollee) {
            Log::critical("Enrollee for postmark inbound data rec_id: $recordId not found");
            sendSlackMessage('#carecoach_ops_alerts', "Enrollee for postmark inbound data rec_id: $recordId not found");

            return false;
        }

        return $enrollee->update([
            'status'                  => Enrollee::TO_CALL,
            'care_ambassador_user_id' => $enrollee->care_ambassador_user_id,
            'requested_callback'      => Carbon::now()->toDate(),
            'callback_note'           => 'Callback automatically scheduled by the system - patient requested callback',
        ]);
    }

    /**
     * @throws \Exception
     * @return \CircleLinkHealth\SharedModels\Entities\Call
     */
    private function assignCallbackToNurse(User $user, PostmarkCallbackInboundData $postmarkCallbackData)
    {
        /** @var SchedulerService $service */
        $service = app(SchedulerService::class);

        return $service->scheduleAsapCallbackTask(
            $user,
            (new AutomatedCallbackMessageValueObject(
                $postmarkCallbackData->get('phone'),
                $postmarkCallbackData->get('message'),
                $user->first_name,
                $user->last_name
            ))->constructCallbackMessage(),
            CpmConstants::SCHEDULER_POSTMARK_INBOUND_MAIL,
            null,
            SchedulerService::CALL_BACK_TYPE,
        );
    }

    private function createUnresolvedInboundCallback(array $matchedResultsFromDB, int $recordId)
    {
        return (new ProcessUnresolvedPostmarkCallback($matchedResultsFromDB, $recordId))->handleUnresolved();
    }
}
