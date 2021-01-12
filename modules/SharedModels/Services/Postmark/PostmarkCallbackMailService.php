<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services\Postmark;

use CircleLinkHealth\SharedModels\Entities\PostmarkInboundCallbackRequest;
use CircleLinkHealth\SharedModels\Entities\PostmarkInboundMail;
use Illuminate\Support\Facades\Log;

class PostmarkCallbackMailService
{
    /**
     *@throws \Exception
     * @return \CirleLinkHealth\Customer\DTO\PostmarkCallbackInboundData|void
     */
    public function postmarkInboundData(int $postmarkRecordId)
    {
        $postmarkRecord = PostmarkInboundMail::where('id', $postmarkRecordId)->first();

        if ( ! $postmarkRecord) {
            Log::critical("Record with id:$postmarkRecordId does not exist in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not locate inbound mail with id:[$postmarkRecordId] in postmark_inbound_mail");

            return;
        }

        if (empty($postmarkRecord->body)) {
            Log::error("Inbound Callback data is empty for inbound_postmark_mail id: [$postmarkRecordId]");
            sendSlackMessage('#carecoach_ops_alerts', "Inbound Callback data is empty for inbound_postmark_mail id: [$postmarkRecordId]");

            return;
        }

        return (new PostmarkInboundCallbackRequest())->run($postmarkRecord->body, $postmarkRecordId);
    }
}
