<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Postmark;

use CircleLinkHealth\SharedModels\DTO\PostmarkCallbackInboundData;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundCallbackRequest;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundMail;
use CircleLinkHealth\SharedModels\Exceptions\CannotParseCallbackException;
use CircleLinkHealth\SharedModels\Exceptions\DailyCallbackReportException;
use Illuminate\Support\Facades\Log;

class PostmarkCallbackMailService
{
    /**
     * @throws CannotParseCallbackException|DailyCallbackReportException
     */
    public function getInboundData(int $postmarkRecordId): ?PostmarkCallbackInboundData
    {
        $postmarkRecord = PostmarkInboundMail::find($postmarkRecordId);

        if ( ! $postmarkRecord) {
            Log::critical("Record with id:$postmarkRecordId does not exist in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not locate inbound mail with id:[$postmarkRecordId] in postmark_inbound_mail");

            return null;
        }

        if (empty($postmarkRecord->body)) {
            Log::error("Inbound Callback data is empty for inbound_postmark_mail id: [$postmarkRecordId]");
            sendSlackMessage('#carecoach_ops_alerts', "Inbound Callback data is empty for inbound_postmark_mail id: [$postmarkRecordId]");

            return null;
        }

        $callerIdCountInString = substr_count($postmarkRecord->body, PostmarkInboundCallbackRequest::INBOUND_CALLER_ID);

        if ($callerIdCountInString === 0) {
            $message = "Inbound Callback: [$postmarkRecordId] is missing [Clr Id]. It cannot be processed.";
            Log::error($message);
            sendSlackMessage('#carecoach_ops_alerts', $message);

            return null;
        }

        if ($callerIdCountInString > 1) {
            throw new DailyCallbackReportException();
        }

        return (new PostmarkInboundCallbackRequest())->process($postmarkRecord->body, $postmarkRecordId);
    }
}
