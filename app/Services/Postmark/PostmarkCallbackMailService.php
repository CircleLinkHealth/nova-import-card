<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Postmark;

use App\PostmarkInboundMail;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostmarkCallbackMailService
{
//    /**
//     * @return array|Builder|\Collection|Collection|Model|object|void|null
//     */
//    public function getMatchedPatients(array $inboundPostmarkData, int $recId)
//    {
//        /** @var Builder $postmarkInboundPatientsMatched */
//        $postmarkInboundPatientsMatched = $this->getPostmarkInboundPatientsByPhone($inboundPostmarkData);
//
//        if ($this->singleMatch($postmarkInboundPatientsMatched)) {
//            return [
//                'patient'        => $postmarkInboundPatientsMatched->first(),
//                'createCallback' => $this->patientIsCallbackEligible(
//                    $postmarkInboundPatientsMatched->first(),
//                    $inboundPostmarkData
//                ),
//            ];
//        }
//
//        if ($postmarkInboundPatientsMatched->count() > 1) {
//            return  $this->filterPostmarkInboundPatientsByName($postmarkInboundPatientsMatched, $inboundPostmarkData);
//        }
//
//        Log::warning("Could not find a patient match for record_id:[$recId] in postmark_inbound_mail");
//        sendSlackMessage('#carecoach_ops_alerts', "Could not find a patient match for record_id:[$recId] in postmark_inbound_mail");
//    }

    /**
     * @return array|void
     */
    public function parsedEmailData(int $postmarkRecordId)
    {
        $postmarkRecord = PostmarkInboundMail::where('id', $postmarkRecordId)->first();
        if ( ! $postmarkRecord) {
            Log::critical("Record with id:$postmarkRecordId does not exist in postmark_inbound_mail");
            sendSlackMessage('#carecoach_ops_alerts', "Could not locate inbound mail with id:[$postmarkRecordId] in postmark_inbound_mail");

            return;
        }

        return collect(json_decode($postmarkRecord->data))->toArray();
    }

    public function shouldCreateCallBackFromPostmarkInbound(array $matchedPatients)
    {
        return $matchedPatients['createCallback'];
    }
    
   
   

   

   
}
