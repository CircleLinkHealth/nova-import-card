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
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\PostmarkMatchedData;
use CircleLinkHealth\SharedModels\Entities\UnresolvedPostmarkCallback;
use CircleLinkHealth\SharedModels\Exceptions\EnrolleeWithoutUserException;
use CircleLinkHealth\SharedModels\Exceptions\NurseNotFoundException;
use CircleLinkHealth\SharedModels\Exceptions\UserWithoutEnrolleeException;
use CircleLinkHealth\SharedModels\Services\SchedulerService;
use Illuminate\Support\Facades\Log;

class AutoResolveCallbackRequestService
{
    public function processCreateCallback(int $recordId)
    {
        try {
            $postmarkCallbackService = app(PostmarkCallbackMailService::class);
            $postmarkCallbackData    = $postmarkCallbackService->getInboundData($recordId);
            if ( ! $postmarkCallbackData) {
                return;
            }

            $matchedResultsFromDB = (new PostmarkInboundCallbackMatchResults($postmarkCallbackData, $recordId))
                ->getMatchResults();

            if (empty($matchedResultsFromDB) || empty($matchedResultsFromDB->matched)) {
                $this->notifySlack("Could not find a patient match for record_id:[$recordId] in postmark_inbound_mail");

                return;
            }

            if (PostmarkInboundCallbackMatchResults::CREATE_CALLBACK === $matchedResultsFromDB->reasoning) {
                $callBackAssignedToNurse = $this->assignCallbackToNurse($matchedResultsFromDB->matched[0], $postmarkCallbackData);
                if ( ! $callBackAssignedToNurse) {
                    $this->notifySlack("Failed to created callback assigned to Nurse. See [$recordId] in inbound_postmark_mail", true);
                }

                return;
            }

            if (PostmarkInboundCallbackMatchResults::NOT_CONSENTED_CA_ASSIGNED === $matchedResultsFromDB->reasoning) {
                $callBackAssignedToCa = $this->assignCallbackToCareAmbassador($matchedResultsFromDB->matched[0]);
                if ( ! $callBackAssignedToCa) {
                    $this->notifySlack("Failed to created callback assigned to CA. See [$recordId] in inbound_postmark_mail", true);
                }

                return;
            }

            $saveUnresolvedCallback = $this->createUnresolvedInboundCallback($matchedResultsFromDB, $recordId);
            if ( ! $saveUnresolvedCallback) {
                $this->notifySlack("Failed to create Unresolved Callback for:[$recordId] in postmark_inbound_mail", true);
            }
        } catch (\Exception $e) {
            $this->notifySlack("{$e->getMessage()}. See inbound_postmark_mail id [$recordId]", true);
        }
    }

    /**
     * @throws UserWithoutEnrolleeException
     */
    private function assignCallbackToCareAmbassador(User $user): bool
    {
        $enrollee = $user->enrollee;
        if ( ! $enrollee) {
            throw new UserWithoutEnrolleeException($user->id);
        }

        return $enrollee->update([
            'status'                  => Enrollee::TO_CALL,
            'care_ambassador_user_id' => $enrollee->care_ambassador_user_id,
            'requested_callback'      => Carbon::now()->toDate(),
            'callback_note'           => 'Callback automatically scheduled by the system - patient requested callback',
        ]);
    }

    /**
     * @throws EnrolleeWithoutUserException|NurseNotFoundException
     */
    private function assignCallbackToNurse(User $user, PostmarkCallbackInboundData $postmarkCallbackData): Call
    {
        if (0 === $user->id) {
            throw new EnrolleeWithoutUserException($user->enrollee->id);
        }

        /** @var SchedulerService $service */
        $service         = app(SchedulerService::class);
        $callbackMessage = (new AutomatedCallbackMessageValueObject(
            $postmarkCallbackData->get('phone'),
            $postmarkCallbackData->get('message'),
            $user->first_name,
            $user->last_name
        ))->toCallbackMessage();

        return $service->scheduleAsapCallbackTask(
            $user,
            $callbackMessage,
            CpmConstants::SCHEDULER_POSTMARK_INBOUND_MAIL,
            null,
            SchedulerService::CALL_BACK_TYPE,
        );
    }

    private function createUnresolvedInboundCallback(PostmarkMatchedData $matchedResultsFromDB, int $recordId): ?UnresolvedPostmarkCallback
    {
        return (new ProcessUnresolvedPostmarkCallback($matchedResultsFromDB, $recordId))->process();
    }

    private function notifySlack(string $msg, bool $error = false): void
    {
        if ($error) {
            Log::error($msg);
        } else {
            Log::warning($msg);
        }
        $testingSlack = forceSendSlackNotifications();
        $slackMessage = $testingSlack ? $msg.' '.'Please Ignore this post [TESTING]' : $msg;
        sendSlackMessage('#carecoach_ops_alerts', $slackMessage, $testingSlack);
    }
}
