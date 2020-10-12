<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\Entities\PostmarkInboundCallbackRequest;
use App\PostmarkInboundMail;
use Illuminate\Support\Facades\Log;

class PostmarkCallbackMailService
{
    /**
     * @return array|void
     */
    public function postmarkInboundData(int $postmarkRecordId)
    {
        $postmarkRecord = PostmarkInboundMail::where('id', $postmarkRecordId)->first();
        if ( ! $postmarkRecord) {
            Log::critical("Record with id:$postmarkRecordId does not exist in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not locate inbound mail with id:[$postmarkRecordId] in postmark_inbound_mail");

            return;
        }

        $inboundDataArray = (new PostmarkInboundCallbackRequest())->run($postmarkRecord->body, $postmarkRecordId);

        if (empty($inboundDataArray)) {
            Log::error("Inbound Callback data is empty for inbound_postmark_mail id: [$postmarkRecordId]");

            return;
        }

        return $inboundDataArray;
    }
}
