<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Services\Calls\SchedulerService;
use CircleLinkHealth\Customer\Entities\User;
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

            if ($matchedResultsFromDB['createCallback']) {
                /** @var SchedulerService $service */
                $service = app(SchedulerService::class);
                $service->scheduleAsapCallbackTask(
                    $matchedResultsFromDB['matchUsersResult'],
                    $postmarkCallbackData['Msg'],
                    'postmark_inbound_mail',
                    null,
                    SchedulerService::CALL_BACK_TYPE
                );

                return;
            }
// Use a flag to know when to aasign to CA or createUnresolvedInboundCallback.
//            if ($postmarkCallbackService->shouldAssignToCareAmbassador($matchedResultsFromDB)) {
//                $x = 1;
//            }
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
    }

    private function createUnresolvedInboundCallback(array $matchedResultsFromDB, int $recordId)
    {
        (new ProcessUnresolvedPostmarkCallback($matchedResultsFromDB, $recordId))->handleUnresolved();
    }
}
