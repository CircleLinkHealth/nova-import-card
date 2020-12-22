<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Entities\PostmarkInboundCallbackRequest;
use App\PostmarkInboundMail;
use CirleLinkHealth\Customer\DTO\PostmarkCallbackInboundData;
use Illuminate\Support\Facades\Log;

class PostmarkCallbackMailService
{
    /**
     * @return \CirleLinkHealth\Customer\DTO\PostmarkCallbackInboundData|void
     *@throws \Exception
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
